# Grandview Hotel CTF - Complete Setup Guide

## Quick Start (5 Minutes)

### Prerequisites Check
- ✅ XAMPP installed and running
- ✅ Web browser available
- ✅ Basic command line knowledge

### Rapid Deployment
1. **Download & Extract**
   - Extract all files to `C:\xampp\htdocs\grandview-hotel\`

2. **Database Setup**
   - Open: http://localhost/phpmyadmin
   - Click "Import" → Select `database.sql` → Click "Go"

3. **Test Access**
   - Navigate to: http://localhost/grandview-hotel/
   - Login with: admin / admin123

---

## Detailed Installation Guide

### Step 1: XAMPP Installation & Configuration

#### Download XAMPP
```
URL: https://www.apachefriends.org/download.html
Version: XAMPP 8.0+ (includes PHP 7.4+, MySQL 5.7+, Apache 2.4+)
```

#### Installation Process
1. **Run installer as Administrator**
2. **Select components:**
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin
   - ❌ Mercury, Tomcat (not needed)

3. **Installation directory:** `C:\xampp\` (default)

#### Start Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Verify green status indicators

#### Verify Installation
```
Apache: http://localhost/ (should show XAMPP dashboard)
phpMyAdmin: http://localhost/phpmyadmin (should load database interface)
```

### Step 2: Database Setup

#### Method 1: Using phpMyAdmin (Recommended)
1. **Access phpMyAdmin**
   ```
   URL: http://localhost/phpmyadmin
   Username: root
   Password: (leave blank)
   ```

2. **Create Database**
   - Click "Databases" tab
   - Database name: `grandview_hotel`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Data**
   - Select `grandview_hotel` database
   - Click "Import" tab
   - Choose file: `database.sql`
   - Click "Go"
   - Verify 5 tables created with sample data

#### Method 2: Using MySQL Command Line
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE grandview_hotel;
USE grandview_hotel;

-- Import SQL file
SOURCE C:/xampp/htdocs/grandview-hotel/database.sql;

-- Verify tables
SHOW TABLES;
```

### Step 3: Application Deployment

#### File Placement
```
Source: [Downloaded files]
Destination: C:\xampp\htdocs\grandview-hotel\

Required Structure:
C:\xampp\htdocs\grandview-hotel\
├── index.php
├── login.php
├── config.php
├── database.sql
├── css\style.css
├── images\ (folder)
└── [all other PHP files]
```

#### Permissions Check (Windows)
- Ensure XAMPP has read/write access to the folder
- If using custom location, verify IIS_IUSRS permissions

#### Configuration Verification
1. **Check config.php**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Empty for default XAMPP
   define('DB_NAME', 'grandview_hotel');
   ```

2. **Test Database Connection**
   - Access: http://localhost/grandview-hotel/
   - Should load without errors

### Step 4: Initial Testing

#### Test User Access
```
URL: http://localhost/grandview-hotel/login.php
Admin: admin / admin123
Customer: john_doe / password123
```

#### Verify Key Functionality
1. **Homepage loads:** http://localhost/grandview-hotel/
2. **Login works:** Authentication successful
3. **Dashboard accessible:** User dashboard loads
4. **Search functional:** Search page responds
5. **Booking system:** Room booking available

#### Common Issues & Solutions

**Issue: "Database connection failed"**
```
Solution:
1. Verify MySQL service is running in XAMPP
2. Check database name in config.php
3. Ensure database was imported correctly
```

**Issue: "404 Not Found"**
```
Solution:
1. Verify files are in correct htdocs folder
2. Check Apache service is running
3. Confirm URL path: http://localhost/grandview-hotel/
```

**Issue: "PHP errors displayed"**
```
Solution:
1. This is normal - application has intentional vulnerabilities
2. Errors are part of the CTF challenge
```

---

## CTF Challenge Preparation

### Creating TryHackMe Room

#### Room Configuration
```
Room Name: Grandview Hotel Security Assessment
Difficulty: Medium
Category: Web Application Security
Estimated Time: 2-3 hours
Machine Type: Web Application
```

#### Room Description Template
```markdown
You've been hired to assess the security of Grandview Hotel's booking system. 
The hotel management wants a thorough security evaluation before going live.

Your objectives:
1. Identify SQL injection vulnerabilities
2. Test authentication mechanisms  
3. Find cross-site scripting flaws
4. Discover access control issues

Capture all flags to complete the assessment!
```

#### Deployment Options

**Option 1: Local XAMPP Setup**
- Provide download link and setup instructions
- Students run locally on their machines
- Best for learning and practice

**Option 2: TryHackMe AttackBox**
- Pre-configured environment with XAMPP
- Students access via browser
- Consistent environment for all users

**Option 3: Docker Container**
```dockerfile
FROM php:7.4-apache
COPY . /var/www/html/
RUN apt-get update && apt-get install -y mysql-server
EXPOSE 80
```

### Student Instructions

#### Pre-Challenge Briefing
```
1. Read the README.md file thoroughly
2. Set up the environment using this guide
3. Test basic functionality before starting
4. Use browser developer tools for debugging
5. Document findings as you progress
```

#### Challenge Rules
```
1. No automated scanning tools (SQLmap, etc.)
2. Manual testing encouraged for learning
3. Document exploitation steps
4. Explain impact of each vulnerability
5. Provide remediation recommendations
```

#### Flag Submission Format
```
Flag format: flag{descriptive_name}
Submit via TryHackMe interface
Include screenshot evidence
Explain discovery method
```

---

## Advanced Configuration

### Security Headers (Optional)
Add to `.htaccess` for additional challenges:
```apache
# Intentionally weak security headers for educational purposes
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "0"
# Note: These are intentionally insecure for CTF purposes
```

### Custom PHP Configuration
Edit `php.ini` for enhanced vulnerability demonstration:
```ini
; Display errors for educational purposes
display_errors = On
error_reporting = E_ALL

; Disable some security features for CTF
allow_url_include = On
magic_quotes_gpc = Off
```

### Database Logging
Enable MySQL query logging for advanced analysis:
```sql
-- Enable general query log
SET GLOBAL general_log = 'ON';
SET GLOBAL general_log_file = 'C:/xampp/mysql/data/queries.log';
```

### SSL Configuration (Optional)
For HTTPS testing scenarios:
```apache
# In httpd-ssl.conf
<VirtualHost *:443>
    DocumentRoot "C:/xampp/htdocs/grandview-hotel"
    ServerName localhost
    SSLEngine on
    SSLCertificateFile "path/to/cert.pem"
    SSLCertificateKeyFile "path/to/private.key"
</VirtualHost>
```

---

## Troubleshooting Guide

### Common Installation Issues

#### XAMPP Won't Start
```
Problem: Apache/MySQL services fail to start
Solutions:
1. Check port conflicts (80, 443, 3306)
2. Run XAMPP as Administrator
3. Disable Windows IIS if installed
4. Check Windows Firewall settings
```

#### Database Import Fails
```
Problem: SQL import shows errors
Solutions:
1. Verify file encoding (UTF-8)
2. Check MySQL version compatibility
3. Import tables individually if needed
4. Verify sufficient disk space
```

#### PHP Errors
```
Problem: White screen or PHP errors
Solutions:
1. Check PHP error logs in XAMPP
2. Verify file permissions
3. Ensure all required files are present
4. Check PHP version compatibility
```

### Performance Optimization

#### For Multiple Students
```
1. Increase Apache MaxRequestWorkers
2. Optimize MySQL buffer pool size
3. Enable PHP OPcache
4. Use SSD storage for better I/O
```

#### Resource Requirements
```
Minimum:
- 2GB RAM
- 1GB free disk space
- Dual-core processor

Recommended:
- 4GB RAM
- 2GB free disk space
- Quad-core processor
```

---

## Educational Integration

### Curriculum Integration

#### Course Alignment
```
Week 1: Web Application Fundamentals
Week 2: SQL Injection (Challenge 1-3)
Week 3: Authentication Security (Challenge 4-5)
Week 4: XSS Vulnerabilities (Challenge 6)
Week 5: Access Control (Challenge 7)
Week 6: Final Assessment & Reporting
```

#### Assessment Rubric
```
Technical Skills (40%):
- Vulnerability identification
- Exploitation techniques
- Tool usage proficiency

Analysis & Documentation (30%):
- Impact assessment
- Risk rating accuracy
- Mitigation recommendations

Professional Skills (20%):
- Report quality
- Communication clarity
- Methodology documentation

Learning Reflection (10%):
- Self-assessment accuracy
- Improvement identification
- Knowledge application
```

### Instructor Resources

#### Grading Guidelines
```
Flag Capture Points:
- Easy flags (1-2): 20 points each
- Medium flags (3-6): 30 points each  
- Hard flags (7): 50 points
- Bonus documentation: 20 points

Total possible: 250 points
Passing score: 150 points (60%)
```

#### Teaching Notes
```
Common Student Challenges:
1. SQL injection syntax errors
2. XSS payload encoding issues
3. IDOR parameter identification
4. Professional report writing

Intervention Strategies:
1. Provide syntax examples
2. Use browser developer tools
3. Explain HTTP request structure
4. Share report templates
```

---

## Security Considerations

### Isolation Requirements
```
⚠️ CRITICAL: Never expose to internet
✅ Use only in isolated lab environments
✅ Implement network segmentation
✅ Monitor for unauthorized access
✅ Regular security awareness training
```

### Clean-up Procedures
```
After CTF completion:
1. Stop XAMPP services
2. Delete database if not needed
3. Remove application files
4. Clear browser data
5. Document lessons learned
```

### Responsible Disclosure
```
If real vulnerabilities are discovered:
1. Report to course instructor immediately
2. Do not exploit beyond educational scope
3. Follow institutional security policies
4. Document findings appropriately
```

---

## Support & Maintenance

### Getting Help
```
Technical Issues:
- Check troubleshooting section
- Review XAMPP documentation
- Contact instructor or TA

Educational Questions:
- Refer to walkthrough guide
- Use course discussion forums
- Schedule office hours

Bug Reports:
- Document steps to reproduce
- Include screenshots/logs
- Submit via course platform
```

### Updates & Maintenance
```
Regular Tasks:
- Update XAMPP as needed
- Refresh sample data periodically
- Monitor for new vulnerability techniques
- Update documentation

Version Control:
- Maintain backup copies
- Track configuration changes
- Document customizations
```

This setup guide ensures students can successfully deploy and access the Grandview Hotel CTF challenge while maintaining educational value and security best practices.









