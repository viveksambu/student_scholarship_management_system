# ScholarMatch Setup Guide

This project is built using HTML/CSS/JS for the frontend and PHP/MySQL for the backend, designed specifically to be run on XAMPP.

## 🚀 How to Run with XAMPP

1. **Move Files**: Ensure this entire folder (`scholarship_portal`) is placed inside your XAMPP's `htdocs` directory. (e.g. `C:\xampp\htdocs\scholarship_portal`).
2. **Start Servers**: Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Database Setup**:
   - Go to phpMyAdmin in your browser: `http://localhost/phpmyadmin`
   - Import the database by either:
     - Selecting the `Import` tab and choosing the `setup.sql` file located in this project directory.
     - OR, click on the "SQL" tab and copy-paste the entire contents of `setup.sql` and click "Go".
4. **Access the App**:
   - Open your web browser and go to: `http://localhost/scholarship_portal/index.html` (or `index.php` if you configured it that way).
   - Once it loads, select a stream from the dropdown to fetch scholarships from your local MySQL database via PHP.

Enjoy the beautifully designed UI!
