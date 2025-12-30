# Grandview Hotel CTF Challenge

## Project Overview

This is a deliberately vulnerable web application designed for cybersecurity education and penetration testing practice. The Grandview Hotel booking system contains multiple security vulnerabilities mapped to the OWASP Top 10 (2021) for TryHackMe CTF challenges.

## ⚠️ WARNING ⚠️

**THIS APPLICATION IS INTENTIONALLY VULNERABLE**

- **DO NOT** deploy this application to a production environment
- **DO NOT** expose this application to the internet
- Use only in controlled, isolated environments for educational purposes
- This application contains serious security vulnerabilities by design

## Features

### Hotel Booking System
- **User Registration & Authentication**
- **Room Browsing & Booking**
- **Customer Dashboard**
- **Admin Panel**
- **Feedback System**
- **Search Functionality**

### Security Vulnerabilities (OWASP Top 10 2021)

1. **SQL Injection (A03:2021 - Injection)**
   - Authentication bypass
   - Data extraction via UNION attacks
   - Database enumeration

2. **Broken Authentication (A07:2021 - Identification and Authentication Failures)**
   - Weak password storage (plain text)
   - Poor session management
   - Information disclosure through error messages

3. **Cross-Site Scripting (A03:2021 - Injection)**
   - Stored XSS in feedback system
   - Reflected XSS possibilities
   - DOM-based XSS vectors

4. **Insecure Direct Object References (A01:2021 - Broken Access Control)**
   - User profile enumeration
   - Unauthorized booking access
   - Admin data exposure

## Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- Text editor (optional)

### Step-by-Step Installation

1. **Download XAMPP**
   ```
   Download from: https://www.apachefriends.org/
   Install and start Apache + MySQL services
   ```

2. **Setup Database**
   ```bash
   # Open phpMyAdmin: http://localhost/phpmyadmin
   # Import database.sql or run the SQL commands manually
   # Verify 'grandview_hotel' database is created
   ```

3. **Deploy Application**
   ```bash
   # Copy all files to: C:\xampp\htdocs\grandview-hotel\
   # Ensure proper file permissions
   ```

4. **Access Application**
   ```
   URL: http://localhost/grandview-hotel/
   ```

### Test Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Customer Accounts:**
- Username: `john_doe` | Password: `password123`
- Username: `jane_smith` | Password: `qwerty456`
- Username: `mike_wilson` | Password: `letmein789`

## CTF Challenges & Flags

### Challenge Structure
The CTF contains 7 hidden flags distributed across different vulnerability types:

| Flag ID | Vulnerability Type | Difficulty | Points |
|---------|-------------------|------------|---------|
| 1-3 | SQL Injection | Medium | 100 each |
| 4-5 | Information Disclosure | Easy | 50 each |
| 6 | Cross-Site Scripting | Medium | 100 |
| 7 | Insecure Direct Object Reference | Hard | 150 |

### Flag Format
All flags follow the format: `flag{descriptive_name}`

### Scoring System
- **Total Points Available:** 650 points
- **Passing Score:** 400 points (60%)
- **Excellence Score:** 550 points (85%)

## File Structure

```
grandview-hotel/
├── index.php              # Homepage
├── login.php              # Authentication (SQL Injection)
├── register.php           # User registration
├── dashboard.php          # User dashboard (IDOR, XSS)
├── admin.php              # Admin panel (Access Control)
├── search.php             # Search functionality (SQL Injection)
├── feedback.php           # Feedback display (XSS)
├── booking.php            # Room booking system
├── rooms.php              # Room listings
├── logout.php             # Session termination
├── config.php             # Database configuration
├── database.sql           # Database schema and data
├── css/
│   └── style.css          # Styling
├── images/                # Image assets (placeholder)
├── WALKTHROUGH.md         # Complete exploitation guide
└── README.md              # This file
```

## Technical Details

### Technology Stack
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Apache 2.4+

### Database Schema
- **users:** User accounts and credentials
- **rooms:** Hotel room information
- **bookings:** Reservation data
- **admin_notes:** Administrative notes (contains flags)
- **feedback:** Customer reviews (XSS target)

### Security Features (Intentionally Minimal)
- Basic session management
- Minimal input validation
- No prepared statements (vulnerable to SQL injection)
- No output encoding (vulnerable to XSS)
- No access controls (vulnerable to IDOR)

## Learning Objectives

### For Students
1. **Vulnerability Identification:** Recognize common web application vulnerabilities
2. **Exploitation Techniques:** Learn practical attack methods
3. **Impact Assessment:** Understand business impact of security flaws
4. **Remediation Planning:** Develop mitigation strategies

### For Educators
1. **Practical Examples:** Real-world vulnerable code samples
2. **Progressive Difficulty:** Challenges scale from easy to advanced
3. **Comprehensive Coverage:** Multiple vulnerability classes
4. **Assessment Ready:** Built-in scoring and validation

## Educational Use Cases

### Cybersecurity Courses
- **Web Application Security**
- **Penetration Testing**
- **Ethical Hacking**
- **Secure Coding Practices**

### Professional Training
- **Bug Bounty Preparation**
- **Security Assessment Skills**
- **Incident Response Training**
- **Developer Security Awareness**

### Certification Preparation
- **CEH (Certified Ethical Hacker)**
- **OSCP (Offensive Security Certified Professional)**
- **GWEB (GIAC Web Application Penetration Tester)**

## Remediation Guide

### SQL Injection Prevention
```php
// Vulnerable Code
$query = "SELECT * FROM users WHERE username = '$username'";

// Secure Code
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

### XSS Prevention
```php
// Vulnerable Code
echo $user_input;

// Secure Code
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### IDOR Prevention
```php
// Vulnerable Code
$user_id = $_GET['user_id'];

// Secure Code
if ($user_id !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    die('Access denied');
}
```

## Advanced Exploitation Scenarios

### Multi-Stage Attacks
1. **Reconnaissance → SQL Injection → Privilege Escalation**
2. **IDOR → Data Extraction → XSS Payload Injection**
3. **Authentication Bypass → Admin Panel Access → System Compromise**

### Automated Testing
```bash
# SQLmap example
sqlmap -u "http://localhost/grandview-hotel/login.php" --data="username=test&password=test" --dbs

# Burp Suite scanning
# Configure proxy and run active scanner
```

## Assessment Criteria

### Bug Bounty Component (50%)
- **Methodology (10%):** Systematic approach, tool selection
- **Vulnerability Discovery (20%):** Finding and documenting flaws
- **Impact Assessment (10%):** Business impact analysis
- **Report Quality (10%):** Professional documentation

### CTF Component (40%)
- **Vulnerability Exploitation (15%):** Successfully capturing flags
- **Technical Understanding (10%):** Demonstrating comprehension
- **Documentation (5%):** Clear step-by-step walkthroughs
- **Creative Solutions (10%):** Novel exploitation techniques

### Reflection Component (10%)
- **Learning Outcomes:** What was learned
- **Challenges Faced:** Difficulties encountered
- **Improvement Areas:** Future development goals

## Support & Resources

### Documentation
- Complete walkthrough in `WALKTHROUGH.md`
- Inline code comments explaining vulnerabilities
- SQL injection cheat sheets in application

### Community
- Report issues via GitHub
- Share learning experiences
- Contribute improvements

### Additional Resources
- **OWASP Top 10:** https://owasp.org/www-project-top-ten/
- **Web Security Academy:** https://portswigger.net/web-security
- **SANS Secure Coding:** https://www.sans.org/secure-coding/

## Legal Notice

This application is provided for educational purposes only. Users are responsible for:
- Using the application ethically and legally
- Obtaining proper authorization before testing
- Following responsible disclosure practices
- Complying with local and international laws

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Submit a pull request
4. Include detailed documentation

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- **OWASP Foundation** for vulnerability classification
- **TryHackMe** for CTF platform inspiration
- **Security Community** for best practices guidance

---

**Version:** 1.0  
**Last Updated:** October 2024  
**Compatibility:** XAMPP 8.0+, PHP 7.4+, MySQL 5.7+









