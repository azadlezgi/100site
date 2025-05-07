# 100site — Multi-Site CMS (PHP-Fusion Modification)

**100site** is a lightweight multi-site content management system developed in 2015 based on **PHP-Fusion**. It enables managing multiple websites using a **single codebase**, with each site connected to a **separate database** depending on the domain.

## 🧩 What is PHP-Fusion?

[PHP-Fusion](https://www.php-fusion.co.uk/) is an open-source lightweight CMS written in PHP. This project is a **modification (fork)** of the original PHP-Fusion system, enhanced with **multi-site support** to allow running multiple websites from the same script.

---

## 🔁 Features

- **Multi-site architecture**: Manage multiple websites using one codebase.
- **Domain-based DB routing**: Automatically switches database based on the domain name.
- **Preserves PHP-Fusion’s core**: All original modules, admin panel, and CMS features remain intact.
- **Separate content and configuration**: Each site stores its own settings and content in its individual database.

---

## 🏗 Project Structure Overview

The structure mostly follows the standard PHP-Fusion layout with added routing logic for dynamic database switching based on domain.

### Key Files:

- `maincore.php` – PHP-Fusion's main core file.
- `config/config_router.php` – Holds domain-specific DB configurations.
- `config.php` – Uses router output to establish DB connection.
- `index.php` – Entry point of the CMS.
- `administration/` – Standard PHP-Fusion admin panel.

---

## ⚙️ Installation Guide

### 1. Upload the project

Upload all files to your web hosting or VPS server.

### 2. Configure domain-based DB routing

Edit the `config/config_router.php` file like this:

```php
return [
    'site1.com' => [
        'db_host' => 'localhost',
        'db_name' => 'site1_db',
        'db_user' => 'root',
        'db_pass' => '',
        'db_prefix' => 'fusion_',
    ],
    'site2.com' => [
        'db_host' => 'localhost',
        'db_name' => 'site2_db',
        'db_user' => 'root',
        'db_pass' => '',
        'db_prefix' => 'fusion_',
    ],
];


3. Create databases

Use the official fusion.sql from PHP-Fusion to initialize each site’s database. This will create necessary tables such as site_settings, users, news, etc.

4. Configure domains

Each domain (or subdomain) should point to the root directory where the script is hosted.

⸻

👨‍💻 Admin and User Panel

The classic PHP-Fusion admin panel is fully functional per site. Each website connects to its own database and has independent content, users, and settings.

⸻

📌 Use Cases
	•	Web studios managing multiple client sites from one CMS instance.
	•	Regional versions of a single website.
	•	Lightweight multi-site setups on shared hosting environments.

⸻

🛡 Security Notes
	•	Add config/config_router.php to .gitignore to avoid exposing DB credentials.
	•	Ensure your server supports PHP 7.x or higher.
	•	It is recommended to migrate to PDO for secure DB connections in the future.

⸻

📃 License

This script is open-source and free to use for personal or commercial projects. Credit to the original author is appreciated. If you make significant changes, please mention it in your fork.

⸻

✍ Author

Azad Lezgi
📧 Email: [azadlezgi@yandex.ru]
🔗 GitHub: github.com/azadlezgi
