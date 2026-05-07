# Kanban KPI Platform

A high-performance internal task management platform designed for productivity tracking and KPI (Key Performance Indicator) calculation. Built with the **TALL Stack** (Tailwind, Alpine.js, Laravel, Livewire) with a focus on premium aesthetics and role-based accountability.

## 🚀 Key Features

-   **Interactive Kanban Board**: Dynamic drag-and-drop interface with real-time status updates.
-   **Task Library (SOP)**: Centralized library for recurring tasks and Standard Operating Procedures.
-   **Accountability System**:
    *   **Task Takeover**: Mechanism for taking over overdue tasks.
    *   **Penalty & Reward**: Automatic point calculations (50% penalty for late tasks, 20% rescue bonus for takeovers).
-   **Role-Based Access Control (RBAC)**: Distinct permissions for Director, Manager, and Staff roles.
-   **Smart Notifications**: Real-time activity bell with human-readable audit logs.
-   **KPI Reporting**: Automated performance point calculation based on completed tasks and difficulty points.
-   **Premium Design**: Genesis Design System (Monochromatic, Modern, and Responsive).

## 🛠 Tech Stack

-   **Framework**: Laravel 13.x
-   **Frontend**: Livewire 3/4 & Alpine.js
-   **Styling**: Custom Vanilla CSS (Design Tokens based)
-   **Database**: SQLite / MySQL
-   **Packages**:
    *   `spatie/laravel-permission`: Role & Permission management.
    *   `spatie/laravel-activitylog`: Audit trail and notifications.

## 📦 Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/user/kanban-kpi.git
    cd kanban-kpi
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Database Migration & Seeding**:
    ```bash
    # This will reset the DB and add demo users/tasks
    php artisan migrate:fresh --seed
    ```

5.  **Build Assets**:
    ```bash
    npm run build
    ```

6.  **Run Dev Server**:
    ```bash
    php artisan serve
    ```

## 🔑 Demo Accounts

Use the following credentials after seeding:

| Role     | Email               | Password   |
| -------- | ------------------- | ---------- |
| Director | `director@kpi.test` | `password` |
| Manager  | `manager@kpi.test`  | `password` |
| Staff    | `staff@kpi.test`    | `password` |

## 📜 Business Logic: Penalty & Reward

-   **Deadline Breach**: Tasks not moved to "Review" within 24 hours of deadline are eligible for **Takeover**.
-   **Penalty**: Original PIC loses **50%** of task difficulty points.
-   **Rescue Bonus**: New PIC receives **20%** additional bonus points upon completion.
-   **KPI Calculation**: (Base Point Rate × Task Difficulty) + Bonuses - Penalties.

---

Designed with ❤️ for High-Performance Teams.
