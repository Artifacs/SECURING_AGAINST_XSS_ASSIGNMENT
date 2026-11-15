# One-to-Many with PDO + User Module (Login / Register / Logout)

Business: Data Engineering Consulting. ONE client has MANY projects.
Parent = `clients`, Children = `projects`, User entity = `users`.

## Run on XAMPP
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this folder to `C:\xampp\htdocs\one_to_many_auth`.
3. http://localhost/phpmyadmin -> **Import** -> `database/schema.sql` -> Go.
4. Open http://localhost/one_to_many_auth/ (you'll be sent to the login page).
5. Click **Register**, create an account, log in, then add clients and projects.
