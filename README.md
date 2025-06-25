# BeanTrack

BeanTrack is a Supply Chain Management System (SCMS) for coffee beans, built with Laravel, MySQL, and Flowbite (Tailwind CSS). It offers role-based dashboards for Factory Administrators, Raw Material Suppliers, Wholesalers, and Vendor Applicants, with features for inventory tracking, order processing, workforce management, vendor validation, demand analytics, and real-time chat. This project uses the [Flowbite-Laravel Starter template](https://github.com/themesberg/tailwind-laravel-starter) for a modern, responsive UI.

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [Timeline](#timeline)
- [License](#license)

## Features
- **Role-Based Dashboards**:
  - **Factory Administrator**: Manage users, inventory, orders, production, workforce, vendors, analytics, reports.
  - **Raw Material Supplier**: Process purchase orders, update inventory, track deliveries, view performance reports.
  - **Wholesaler**: Browse catalogs, place orders, track deliveries, access demand analytics.
  - **Vendor Applicant**: Submit applications, upload PDFs, track status, schedule visits.
- **Inventory Management**: Real-time tracking of raw and roasted coffee beans.
- **Order Processing**: Handle incoming (supplier) and outgoing (wholesaler) orders with status updates.
- **Workforce Management**: Assign workers to supply centers with role-based assignments (e.g., packer, supervisor).
- **Vendor Validation**: Manage applications with JSON data and visit scheduling.
- **Demand Analytics**: ML-driven demand predictions and customer segmentation using Chart.js.
- **Real-Time Chat**: User messaging via Laravel Echo and Pusher.
- **Reports**: Scheduled inventory, order, and performance reports.

## Tech Stack
- **Backend**: Laravel 10.x, MySQL 8.x
- **Frontend**: Flowbite, Tailwind CSS, Blade templates
- **Analytics**: Chart.js
- **Real-Time Features**: Laravel Echo, Pusher
- **Version Control**: Git (GitHub)

## Project Structure

BeanTrack/
├── app/                    # Laravel application logic (Models, Controllers)
├── database/               # Migrations, seeders
│   └── migrations/         # Database schema migrations
├── public/                 # Public assets
│   └── build/              # Compiled CSS/JS (Tailwind/Flowbite)
├── resources/              # Blade templates, assets
│   ├── css/                # Tailwind/Flowbite styles
│   ├── js/                 # Flowbite and custom JS
│   └── views/              # Blade templates
├── routes/                 # Web and API routes
├── scms_schema.sql         # MySQL schema with entities
├── tailwind.config.js      # Tailwind/Flowbite configuration
├── vite.config.js          # Vite build configuration
├── README.md               # This file
└── .env.example            # Environment configuration template


## Setup Instructions
### Prerequisites
- PHP 8.1+
- Composer
- Node.js 16+ and npm
- MySQL 8.x
- Git

### Installation
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/<your-username>/BeanTrack.git
   cd BeanTrack
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js Dependencies**:
   ```bash
   npm install
   ```

4. **Configure Environment**:
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with MySQL credentials:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=scms
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```
   - Generate application key:
     ```bash
     php artisan key:generate
     ```

5. **Set Up Database**:
   - Create the `scms` database in MySQL:
     ```sql
     CREATE DATABASE scms;
     ```
   - Import `scms_schema.sql` using a MySQL client (e.g., phpMyAdmin, terminal):
     ```bash
     mysql -u root -p scms < scms_schema.sql
     ```
   - Alternatively, run migrations (if migrations are created):
     ```bash
     php artisan migrate
     ```

6. **Build Frontend Assets**:
   ```bash
   npm run dev
   ```
   - For production: `npm run build`

7. **Start the Server**:
   ```bash
   php artisan serve
   ```
   - Access at `http://localhost:8000`.

## Database Schema
The `scms` database includes 12 entities (see `scms_schema.sql`):
- **Users**: Authenticated users (admin, supplier, wholesaler, applicant).
- **Workers**: Non-authenticated workers for workforce management.
- **SupplyCenters**: Facilities for inventory and workforce.
- **Inventory**: Tracks coffee stock.
- **InventoryUpdates**: Logs inventory changes.
- **Orders**: Manages purchase orders.
- **OrderTrackings**: Tracks order deliveries.
- **WorkforceAssignments**: Assigns workers to supply centers.
- **Messages**: Stores chat messages.
- **Reports**: Scheduled reports.
- **VendorApplications**: Manages vendor applications.
- **AnalyticsData**: Stores ML outputs.

## Contributing
1. **Fork and Branch**:
   - Create a feature branch: `git checkout -b feature/your-feature`.
   - Work on assigned tasks (e.g., UI, backend).

2. **Commit Changes**:
   - Use clear messages: `git commit -m "Add workforce UI with Flowbite"`.
   - Push: `git push origin feature/your-feature`.

3. **Pull Request**:
   - Open a PR to `main` with a detailed description.
   - Tag reviewers.

4. **Issues**:
   - Check Issues for tasks (e.g., “Implement order CRUD”).
   - Assign yourself or request assignment.

5. **Standards**:
   - Follow PSR-12 for PHP.
   - Use Flowbite for UI consistency.
   - Document with PHPDoc.

## Timeline
- **May 30, 2025**: Finalize database schema and initial UI.
- **June 6, 2025**: Submit design document.
- **July 10, 2025**: Complete UI and backend.
- **July 15, 2025**: Test with Laravel Dusk and QA.
- **July 20, 2025**: Final submission and demo.

## License
[MIT License](LICENSE)





## Basic laravel setup
# Laravel 12 and Tailwind CSS Starter

[Check out this guide](https://flowbite.com/docs/getting-started/laravel/) to learn how to set up a new Laravel project together with Tailwind CSS and the UI components from Flowbite to enhance your front-end development workflow.

## Create a Laravel app

Make sure that you have <a href="https://getcomposer.org/" rel="nofollow">Composer</a> and <a href="https://nodejs.org/en/" rel="nofollow">Node.js</a> installed locally on your computer.

Follow the next steps to install Tailwind CSS and Flowbite with Laravel Mix. 

1. Require the Laravel Installer globally using Composer:

```bash
composer global require laravel/installer
```

Make sure to place the vendor bin directory in your PATH. Here's how you can do it based on each OS:

- macOS: `export PATH="$PATH:$HOME/.composer/vendor/bin"`
- Windows: `set PATH=%PATH%;%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`
- Linux: `export PATH="~/.config/composer/vendor/bin:$PATH"`

2. Create a new project using Laravel's CLI:

```bash
laravel new flowbite-app
cd flowbite-app
```

Start the development server using the following command:

```bash
composer run dev
```

You can now access the Laravel application on `http://localhost:8000`.

This command will initialize a blank Laravel project that you can get started with.

## Install Tailwind CSS

Since Laravel 12, the latest version of Tailwind v4 will be installed by default, so if you have that version or later then you can skip this step and proceed with installing Flowbite.

1. Install Tailwind CSS using NPM:

```javascript
npm install tailwindcss @tailwindcss/vite --save-dev
```

2. Configure the `vite.config.ts` file by importing the Tailwind plugin:

```javascript
import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
export default defineConfig({
  plugins: [
    tailwindcss(),
    // …
  ],
})
```

3. Import the main Tailwind directive inside your `app.css` CSS file:

```css
@import "tailwindcss";
```

5. Run the build process for Vite using `npm run dev`. Use `npm run build` for production builds.

## Install Flowbite

[Flowbite](https://flowbite.com) is a popular and open-source UI component library built on top of the Tailwind CSS framework that allows you to choose from a wide range of UI components such as modals, drawers, buttons, dropdowns, datepickers, and more to make your development workflow faster and more efficient.

Follow the next steps to install Flowbite using NPM.

1. Install Flowbite as a dependency using NPM by running the following command:

```bash
npm install flowbite --save
```

2. Import the default theme variables from Flowbite inside your main `input.css` CSS file:

```css
@import "flowbite/src/themes/default";
```

3. Import the Flowbite plugin file in your CSS:

```css
@plugin "flowbite/plugin";
```

4. Configure the source files of Flowbite in your CSS:

```css
@source "../../node_modules/flowbite";
```

5. Add the Flowbite JS script inside your main `app.blade.php` layout file:

```html
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
```

This will have the JavaScript loaded in all the files that extend this main layout.

## UI components

Now that you have successfully installed the project you can start using the [UI components from Flowbite](https://flowbite.com/docs/getting-started/laravel/) and Tailwind CSS to develop modern websites and web applications.

We recommend exploring the components using the search bar navigation (`cmd` or `ctrl` + `k`) or by browsing the components section of the sidebar on the left side of this page.
