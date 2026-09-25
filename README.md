<p align="center">
<a href="https://laravel.com" target="_blank">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</a>
</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions">
<img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
</a>
<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
</a>
<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
</a>
<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
</a>
</p>

# 🎬 Torrent Tracker — Laravel 12

A modern, feature-rich **private torrent tracker and media platform** built with **Laravel 12** and **PHP 8.4**.

The project combines a powerful torrent management system with a growing media library, community features, user management, rewards, messaging, chat, seedbox integration, and online media functionality.

The goal is to create more than just a traditional torrent tracker — it is designed as a complete **torrent, media, and community platform**.

---


<p align="center">
  <a href="https://fileiplay.org">
    <img src="https://img.shields.io/badge/🌐_Visit_FileIplay-fileiplay.org-0d6efd?style=for-the-badge" alt="Visit FileIplay">
  </a>
</p>

<p align="center">
  <strong>🌐 Live Website:</strong>
  <a href="https://fileiplay.org">https://fileiplay.org</a>
</p>

## 🚀 Main Features

### 🧲 Torrent System

A complete torrent management system supporting both single-file and multi-file torrents.

* Single-file and multi-file torrents
* Torrent uploading and publishing
* Torrent categories
* Torrent descriptions
* Torrent file management
* Seeder and leecher statistics
* Completed/download statistics
* Torrent comments and discussions
* Torrent search and filtering
* Torrent browsing and sorting
* User upload/download statistics
* Custom torrent announce system
* Peer tracking

### ⚡ Freeleech & Double Upload

The tracker supports special torrent modes designed to reward users and encourage seeding.

* **Freeleech** — downloads do not count toward a user's download amount
* **Double Upload** — uploaded amount can be counted at 2×
* Ability to apply special torrent statuses
* Fully integrated with the tracker statistics

These features can be used for special releases, promotions, events, or selected torrents.

### 🏆 Seed Bonus System

Users can earn **Seed Bonus Points** by keeping torrents seeded.

The bonus system is configurable and can limit the number of torrents that generate bonus points.

This encourages users to keep torrents active and contribute back to the community.

### 🎉 Happy Hour System

The tracker includes a configurable **Happy Hour** system.

During a selected period, torrent activity can receive increased rewards.

Depending on the configuration:

* Upload credit can be multiplied up to **8×**
* Seed Bonus Points can be multiplied up to **8×**
* The system operates automatically during the configured period

This provides additional incentives for users to seed during selected times.

### 🎁 Invite System

A fully functional **user invitation system** allows members to invite trusted users to the tracker.

The invite system can be enabled or disabled by administrators.

Features include:

* User invitations
* Invite codes
* Invite tracking
* Controlled registration
* Invite management

### 👤 User Management

Users can belong to different classes or ranks based on configurable criteria.

The tracker can automatically manage user classes based on factors such as:

* Upload/download ratio
* Account age
* User activity
* Community participation

This provides a flexible foundation for creating different user levels and permissions.

### 📋 Snatch List

The **Snatch List** gives users an overview of their torrent activity.

It can show torrents that a user:

* Has downloaded
* Is currently seeding
* Has previously seeded
* Needs to reseed

This makes it easier for users to manage their torrent history and maintain their seeding activity.

### 📡 Custom Announce System

The tracker includes a dedicated **custom announce system** designed specifically for the application.

It handles peer communication and torrent activity while allowing the tracker to maintain detailed information about torrent and user activity.

### 💬 Private Messaging

Users can communicate privately using the built-in **messaging system**.

Private messages can be used for:

* Talking with other members
* Discussing releases
* Requesting help
* Community communication
* General conversations

### 💭 Community Chat

The tracker also provides a **community chat system**, allowing users to communicate directly within the website.

Chat can be used for:

* General discussion
* Release discussions
* Help and support
* Community interaction
* Real-time conversations

### 📦 Seedbox System

Users can connect their own **seedboxes** to the tracker.

The seedbox system is designed to make it easier for users to manage their torrent activity directly from the tracker.

Depending on the configured integration, users can:

* Connect a seedbox
* Manage connected seedboxes
* Send torrents to their seedbox
* Monitor torrent activity
* Manage seeding
* Keep torrents seeding automatically

### 🎬 Movies & Series Library

The tracker includes a dedicated **Movies & Series library** for browsing and discovering media.

Users can explore:

* 🎬 Movies
* 📺 TV Series
* 📀 Seasons
* 🎞️ Episodes
* 🎭 Genres
* 🖼️ Posters and artwork
* ℹ️ Media information
* 🔎 Search and filtering

The library provides a more visual experience than a traditional torrent index.

### ▶️ Online Movies & Series

The platform also supports **online media functionality**, allowing supported movies and series to be viewed directly through the website.

This creates a combined experience where users can:

**Discover → Download → Seed → Watch**

all from the same platform.

### 👥 User Profiles

Each member has their own profile containing information about their account and activity.

Depending on the user's permissions and configuration, profiles can display:

* Username
* Profile image
* Registration date
* Last activity
* Upload statistics
* Download statistics
* Share ratio
* Seeding activity
* Account information

### 📊 User Statistics

The tracker keeps detailed statistics about user activity.

Statistics can include:

* Total uploaded
* Total downloaded
* Share ratio
* Seeded torrents
* Active torrents
* Completed torrents
* Seed Bonus Points
* Torrent activity

---

# 🛠️ Built With Laravel

This project is built using **Laravel 12**, providing a modern and powerful foundation for the tracker.

Laravel provides the project with features such as:

* MVC architecture
* Authentication
* Routing
* Middleware
* Eloquent ORM
* Database migrations
* Queues and background jobs
* Events
* Notifications
* Blade templates
* Artisan commands
* Task scheduling
* Cache and session management

Laravel allows the tracker to remain organised, maintainable, and easy to expand as new features are developed.

---

# ⚙️ Technology

* **Laravel 12**
* **PHP 8.4**
* **MySQL**
* **Blade**
* **Bootstrap**
* **JavaScript**
* **Laravel Artisan**
* **Laravel Scheduler**
* **Custom Torrent Announce System**

---

# 🎯 Project Goals

The goal of this project is to build a complete **torrent and media community platform**, rather than simply providing a torrent index.

The platform brings together:

> **Torrents + Media Library + Online Viewing + Seedboxes + Community + Messaging + Chat + Rewards**

The project is continuously evolving, with new features, improvements, optimisations, and integrations being added over time.

---

# 🤝 Contributing

If you would like to contribute to the project, improvements, bug fixes, feature suggestions, and pull requests are welcome.

Feel free to explore the project and contribute where you can.

---

# 💻 Installation & Hosting

The project is designed to run on a server capable of supporting Laravel 12 and PHP 8.4.

If you are interested in installing this tracker on your own server or to help with its development, please contact:

**📧 [luci_calapodescu@yahoo.com](mailto:luci_calapodescu@yahoo.com)**

---

# 📸 Screenshots

<img width="1642" height="860" alt="Trending and online" src="https://github.com/user-attachments/assets/46b818b9-b44a-43bf-ba1e-dd9cfd3652f3" />
<img width="1664" height="851" alt="Torrent details" src="https://github.com/user-attachments/assets/32f372ec-73af-439a-acc1-95112c017403" />
<img width="1970" height="886" alt="RSS" src="https://github.com/user-attachments/assets/163cd4a3-1a8e-4f39-9137-33ef0a8c02a1" />
<img width="1640" height="855" alt="profile" src="https://github.com/user-attachments/assets/2d417ab3-d4ea-4b62-ab61-c9155ebb3473" />
<img width="1650" height="724" alt="News and Poll section" src="https://github.com/user-attachments/assets/f1a2f14c-85e9-43b4-95c2-77dd100b81b8" />
<img width="1497" height="734" alt="forum" src="https://github.com/user-attachments/assets/0f01eaa9-cc2e-4a6d-b1d5-946cfb32fe79" />
<img width="1657" height="734" alt="Chat and online users" src="https://github.com/user-attachments/assets/fd089bc5-6d5a-4908-b920-30a231843c16" />

More screenshots will be added as the project continues to evolve.

---

# 📜 License

The Laravel framework is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).

The licensing terms of this project and its individual components may differ from those of the Laravel framework. Please check the repository for the applicable project license and third-party dependencies.
