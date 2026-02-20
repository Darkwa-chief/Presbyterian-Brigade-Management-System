# Presbyterian Brigade Management System
A comprehensive web-based management system built to digitize and streamline brigade operations within the Presbyterian Church of Ghana.

This system replaces traditional paper-based administrative processes with a structured, role-based digital platform that manages brigaders, attendance, ranks, events, recitals, and notifications efficiently.

---

 📖 Overview

The Presbyterian Brigade Management System is designed to support brigade leaders, officers, and members in managing administrative operations digitally.  

The system provides structured role-based access control, centralized data management, audit tracking, and a modular architecture built with clean PHP OOP principles.

---

  Core Objectives

- Digitize brigade record keeping
- Automate attendance tracking
- Manage ranks and promotions systematically
- Centralize event and recital management
- Provide structured user role permissions
- Improve communication via notifications
- Maintain an organized database structure

---

  System Architecture

The project follows a structured modular architecture:

The system separates concerns clearly:
- Business logic in `classes/`
- Configuration in `config/`
- Routing/UI inside `public/`
- API endpoints under `public/api/`

---

## 👥 Role-Based Access Control

The system supports three primary roles:

### 🔹 Admin
- Manage users (create/edit/delete)
- Manage brigaders
- Manage ranks and promotions
- Manage attendance
- Manage events & recitals
- View and clear audit logs
- System-wide notifications

### 🔹 Officer
- Manage attendance
- Manage brigaders
- Manage events
- Manage recitals

### 🔹 Member
- View personal dashboard
- View events
- View recitals
- Update profile information

---

## ⚙️ Features

###  User Management
- Role-based authentication
- Login / Logout system
- Password change
- Profile settings

###  Brigader Management
- Create brigader profiles
- Update brigader details
- Assign ranks
- Track promotion history

###  Attendance Management
- Record attendance
- View attendance history
- Officer-level attendance control

###  Rank & Promotion System
- Structured rank hierarchy
- Controlled promotion validation
- Promotion audit tracking

###  Event Management
- Create brigade events
- Update event details
- Assign participation

###  Recital Management
- Record recital activities
- View recital logs

###  Notification System
- Notification creation
- Mark as read
- Unread count API
- Notification clearing endpoints

###  Audit Log APIs
- Delete individual audit log
- Clear all audit logs

---

##  Technology Stack

- **Backend:** PHP (Object-Oriented Programming)
- **Database:** MySQL (PDO)
- **Frontend:** HTML, CSS, JavaScript
- **Architecture:** Modular MVC-inspired structure
- **Authentication:** Custom session-based system
- **Database Schema:** Provided via `schema.sql`

---

##  Database

The database schema is included in:


public/schema.sql


To set up:

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE presbyterian_brigade;

Import the schema.sql file.

Update your config/database.php if necessary.

  Installation Guide

Clone the repository:

git clone https://github.com/yourusername/presbyterian-brigade-management-system.git

Move project into your server directory:

XAMPP: htdocs/

WAMP: www/

Import the database schema.

Configure database settings in:

config/database.php

Start Apache & MySQL.

Visit:

http://localhost/presbyterian-brigade/public

🔐 Security Features

PDO prepared statements (prevents SQL injection)

Role-based middleware

Session authentication

Input validation

Structured API endpoints

🔮 Future Improvements

SMS notification integration

Email notification integration

Two-factor authentication

Dashboard analytics & reporting

Deployment to cloud hosting

RESTful API expansion

Improved UI/UX modernization

  Development Practices

Git version control

Feature branch workflow

Modular OOP structure

Clean separation of concerns

Scalable architecture design


 Screenshots

<img width="1890" height="903" alt="dashboard" src="https://github.com/user-attachments/assets/fc647468-a57c-42cc-96b2-ec7bf32987bc" />
<img width="1882" height="908" alt="login" src="https://github.com/user-attachments/assets/713a49a2-9afe-41e9-bfc1-0ebffa858b02" />
<img width="1873" height="901" alt="event" src="https://github.com/user-attachments/assets/08ceb98e-8608-4d28-9877-ae33d10248e1" />




 Contribution

This project is currently maintained as an independent development initiative. Contributions, suggestions, and improvements are welcome.

 License

This project is for educational and organizational use within brigade administrative operations.

 Author

Developed by [DARKWAH GYIMAH ISHMAEL]

Computer Science Student - GHANA COMMUNICATION TECHNOLOGY

Passionate about building systems that solve real organizational problems.
