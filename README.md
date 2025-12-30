## 🏨 Room Description – Grandview Hotel Web Security CTF

**Room URL: https://tryhackme.com/jr/grandviewhotelctfescape.

**Grandview Hotel – Web Security CTF** is a hands-on TryHackMe room designed to simulate a **real-world web application security assessment**.  
The room is built around a deliberately vulnerable hotel booking system that reflects **common security mistakes found in production web applications**.

In this room, you assume the role of a **penetration tester** hired to evaluate the security posture of the Grandview Hotel booking platform before public deployment. Your objective is to **identify, exploit, and understand multiple web vulnerabilities**, while learning **how attackers abuse them and how defenders should fix them**.

Unlike automated scanning-focused labs, this room emphasizes **manual testing**, **logic flaws**, and **understanding root causes**, aligning closely with **real penetration testing and bug bounty methodologies**.

The vulnerabilities demonstrated are mapped directly to the **OWASP Top 10 (2021)**, making this room ideal for learners preparing for:
- Web penetration testing
- Bug bounty programs
- Blue team & defensive security roles
- Academic cybersecurity projects

---

## 🎯 What You Will Learn From This Room

By completing this room, you will gain practical experience in:

- Web application reconnaissance and attack surface mapping
- Identifying insecure coding practices
- Exploiting authentication and authorization weaknesses
- Understanding how improper input handling leads to severe vulnerabilities
- Thinking like both an attacker and a defender

---

## 🧪 Attack & Vulnerability Explanations

Below is a **detailed explanation of each vulnerability covered in this room**, focusing on **how the attack works**, **why it exists**, and **its real-world impact**.

---

### 🔴 SQL Injection (Authentication Bypass)

**OWASP Category:** A03:2021 – Injection

SQL Injection occurs when an application **directly inserts user input into SQL queries without proper validation or parameterization**.

In this room, the login functionality fails to sanitize input properly. By injecting SQL syntax into the username field, an attacker can **manipulate the query logic**, bypassing password verification entirely.

#### Why This Is Dangerous
- Allows attackers to log in as any user (including admin)
- Can lead to full database compromise
- Often results in data breaches and privilege escalation

#### Real-World Impact
Attackers can gain unauthorized administrative access, modify data, or completely destroy backend databases.

---

### 🔴 SQL Injection (UNION-Based Data Extraction)

**OWASP Category:** A03:2021 – Injection

Beyond authentication bypass, SQL Injection can be escalated to **extract sensitive data** using `UNION SELECT` statements.

In this room, a vulnerable search parameter allows attackers to **merge malicious queries with legitimate ones**, exposing:
- Usernames
- Passwords
- Emails
- Roles and account status

#### Why This Is Dangerous
- Exposes sensitive user data
- Enables credential reuse attacks
- Can reveal internal database structure

#### Real-World Impact
This type of vulnerability is commonly exploited in **mass data leaks** and **credential dumping attacks**.

---

### 🔴 Information Disclosure

**OWASP Category:** A02:2021 – Cryptographic Failures / Sensitive Data Exposure

Information Disclosure happens when applications **expose sensitive data unintentionally**, such as:
- Configuration files
- Hardcoded secrets
- Debug comments in source code

In this room, sensitive information is exposed through:
- HTML comments
- Accessible configuration files

#### Why This Is Dangerous
- Reveals internal application logic
- Leaks credentials or API keys
- Helps attackers chain further attacks

#### Real-World Impact
Information disclosure often acts as a **stepping stone** for larger compromises.

---

### 🔴 Broken Authentication

**OWASP Category:** A07:2021 – Identification and Authentication Failures

Broken authentication occurs when authentication mechanisms are implemented incorrectly.

In this room, issues include:
- Plain-text password storage
- Weak session management
- Session IDs not regenerating after login

#### Why This Is Dangerous
- Enables session hijacking
- Allows attackers to impersonate users
- Increases impact of other vulnerabilities

#### Real-World Impact
Broken authentication flaws frequently lead to **account takeovers** and **privilege escalation**.

---

### 🔴 Stored Cross-Site Scripting (XSS)

**OWASP Category:** A03:2021 – Injection

Stored XSS occurs when **malicious JavaScript is permanently stored on the server** and executed whenever a victim views the affected page.

In this room, user input in the feedback system is **not sanitized or encoded**, allowing attackers to store executable scripts.

#### Why This Is Dangerous
- Executes in victims’ browsers
- Can steal cookies and session tokens
- Enables phishing and account takeover

#### Real-World Impact
Stored XSS is especially severe because it **affects all users** who access the compromised page, including administrators.

---

### 🔴 Insecure Direct Object Reference (IDOR)

**OWASP Category:** A01:2021 – Broken Access Control

IDOR occurs when applications **trust user-supplied identifiers** (such as user IDs) without verifying authorization.

In this room, changing a URL parameter allows users to **access other users’ booking details**.

#### Why This Is Dangerous
- Breaks data confidentiality
- Allows horizontal and vertical privilege escalation
- Often affects APIs and dashboards

#### Real-World Impact
IDOR vulnerabilities are commonly exploited in **data privacy breaches** involving personal and financial data.

---

## 🧭 How to Complete the Tasks

This README **intentionally does not include step-by-step exploitation commands**.

📄 **If you want a full guided walkthrough**, including payloads, explanations, and task-by-task instructions,  
👉 **refer to the PDF guide provided in this repository**.

The PDF explains:
- Each task in detail
- Why the attack works
- How to identify similar vulnerabilities in real applications
- Defensive remediation techniques

---

## 🛡️ Defensive Takeaway

This room highlights why secure development practices are critical:

- Always use prepared statements
- Never trust user input
- Enforce server-side authorization
- Properly encode output
- Secure session handling
- Remove debug information before deployment

Understanding these vulnerabilities is essential not just for attackers, but for **building secure applications and defending real-world systems**.

---

⚠️ **Educational Use Only**  
This room and application are intentionally vulnerable and must only be used in controlled lab environments.
