# BeanTrack

BeanTrack is a Supply Chain Management System (SCMS) for coffee beans, built with Laravel, MySQL, and Flowbite (Tailwind CSS). It offers role-based dashboards for Factory Administrators, Raw Material Suppliers, Wholesalers, and Vendor Applicants, with features for inventory tracking, order processing, workforce management, vendor validation, demand analytics, and real-time chat. This project uses the [Flowbite-Laravel Starter template](https://github.com/themesberg/tailwind-laravel-starter) for a modern, responsive UI.

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Real-Time Chat Setup](#real-time-chat-setup)
- [Testing the Chat System](#testing-the-chat-system)
- [Database Schema](#database-schema)
- [Troubleshooting](#troubleshooting)
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
- **Backend**: Laravel 11.x, MySQL 8.x
- **Frontend**: Flowbite, Tailwind CSS, Blade templates, JavaScript ES6+
- **Real-Time**: Laravel Echo, Pusher WebSockets
- **Analytics**: Chart.js
- **Build Tools**: Vite, npm
- **Queue System**: Redis (recommended) or Database
- **Version Control**: Git (GitHub)

## Project Structure

```
BeanTrack/
├── app/                    # Laravel application logic
│   ├── Http/
│   │   └── Controllers/    # Controllers (ChatController, etc.)
│   ├── Models/             # Eloquent models (User, Message, etc.)
│   ├── Events/             # Events (MessageSent, etc.)
│   ├── Livewire/           # Livewire components
│   └── Helpers/            # Helper classes (MenuHelper, etc.)
├── database/               # Database files
│   ├── migrations/         # Database schema migrations
│   ├── seeders/            # Database seeders
│   └── factories/          # Model factories
├── documentation/          # Project documentation
│   ├── chat-system-implementation.md
│   └── real-time-chat-debugging-guide.md
├── public/                 # Public web assets
│   ├── js/                 # Frontend JavaScript (chat.js, etc.)
│   ├── images/             # Static images
│   └── build/              # Compiled assets (Vite output)
├── resources/              # Frontend resources
│   ├── css/                # Stylesheets (Tailwind/Flowbite)
│   ├── js/                 # JavaScript source files (Echo, etc.)
│   └── views/              # Blade templates
│       ├── chat/           # Chat-related views
│       ├── components/     # Reusable Blade components
│       └── layouts/        # Layout templates
├── routes/                 # Application routes
│   ├── web.php             # Web routes (chat routes, etc.)
│   ├── api.php             # API routes
│   └── channels.php        # Broadcasting channels
├── storage/
│   └── logs/               # Application logs
├── config/                 # Configuration files
│   ├── broadcasting.php    # Pusher/Echo configuration
│   └── queue.php           # Queue configuration
├── .env.example            # Environment configuration template
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
├── tailwind.config.js      # Tailwind/Flowbite configuration
├── vite.config.js          # Vite build configuration
└── README.md               # This file
```


## Setup Instructions

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 16+ and npm
- MySQL 8.x
- Git
- **Pusher Account** (for real-time chat functionality) - [Sign up at pusher.com](https://pusher.com)

### Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/<your-username>/BeanTrack.git
cd BeanTrack
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies  
npm install
```

#### 3. Environment Configuration
Copy the environment file and configure your settings:
```bash
cp .env.example .env
```

Update your `.env` file with the following configurations:

**Database Configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scms
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Application Key:**
```bash
php artisan key:generate
```

#### 4. Database Setup
Create and set up your database:

```sql
# Create database in MySQL
CREATE DATABASE scms;
```

Run the migrations to create all tables:
```bash
php artisan migrate
```

**Optional:** Seed the database with sample data:
```bash
php artisan db:seed
```

#### 5. Build Frontend Assets
```bash
# For development (with hot reload)
npm run dev

# For production
npm run build
```

#### 6. Start the Development Server
```bash
php artisan serve
```

Your application will be available at `http://localhost:8000`

## Real-Time Chat Setup

The BeanTrack application includes a robust real-time chat system. Follow these steps to set it up:

### 1. Pusher Configuration

**Sign up for Pusher:**
1. Go to [pusher.com](https://pusher.com) and create a free account
2. Create a new app in your Pusher dashboard
3. Go to "App Keys" section and copy your credentials

**Configure Pusher in `.env`:**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key  
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 2. Queue Configuration

The chat system uses Laravel's queue system for broadcasting. Configure Redis (recommended) or database queues:

**For Redis (Recommended):**
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**For Database Queues (Alternative):**
```env
QUEUE_CONNECTION=database
```

Create the jobs table if using database queues:
```bash
php artisan queue:table
php artisan migrate
```

### 3. Start Queue Workers

**Important:** Queue workers must be running for real-time chat to work:

```bash
# Start queue worker (keep this running)
php artisan queue:work

# Alternative: Use supervisor in production
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### 4. Install Echo and Pusher-js

The required packages should already be installed, but verify:
```bash
npm install laravel-echo pusher-js
```

## Testing the Chat System

### Local Testing Setup

#### 1. Start Required Services
Make sure these are running simultaneously:

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker (REQUIRED for real-time messaging)
php artisan queue:work

# Terminal 3: Start Vite dev server (for asset compilation)
npm run dev
```

#### 2. Create Test Users

Use Laravel Tinker to create test users:
```bash
php artisan tinker
```

```php
// Create test users in Tinker
use App\Models\User;

// Create admin user
User::create([
    'id' => 'U00001',
    'name' => 'Admin User',
    'email' => 'admin@beantrack.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);

// Create supplier user
User::create([
    'id' => 'U00002', 
    'name' => 'Supplier User',
    'email' => 'supplier@beantrack.com',
    'password' => bcrypt('password'),
    'role' => 'supplier'
]);

// Create vendor user
User::create([
    'id' => 'U00003',
    'name' => 'Vendor User', 
    'email' => 'vendor@beantrack.com',
    'password' => bcrypt('password'),
    'role' => 'vendor'
]);
```

#### 3. Test Real-Time Messaging

**Multi-Browser Testing:**
1. Open two different browsers (e.g., Chrome and Firefox)
2. Login as different users in each browser:
   - Browser 1: Login as `admin@beantrack.com` 
   - Browser 2: Login as `supplier@beantrack.com`
3. Navigate to `/chat` in both browsers
4. Start a conversation between the users

**What to expect:**
- Messages should appear instantly in both browsers
- Unread message badges should update in real-time
- Check browser console for any JavaScript errors

#### 4. Testing Checklist

✅ **Basic Functionality:**
- [ ] Users can send messages
- [ ] Messages appear in chat history
- [ ] Messages are saved to database

✅ **Real-Time Features:**
- [ ] Messages appear instantly without page refresh
- [ ] Unread badge updates automatically
- [ ] Multiple users can chat simultaneously

✅ **Error Handling:**
- [ ] Messages still save if real-time fails
- [ ] Graceful degradation when Pusher is unavailable
- [ ] Proper error messages for failed sends

#### 5. Debug Tools

**Enable Chat Debug Mode:**
Add this to your browser console to enable detailed logging:
```javascript
localStorage.setItem('chat_debug', 'true');
// Refresh the page
```

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Verify Database:**
```sql
-- Check messages are being saved
SELECT * FROM messages ORDER BY created_at DESC LIMIT 10;

-- Check queue jobs
SELECT * FROM jobs;
```

### Production Testing

When deploying to production:

1. **Update Pusher Settings:** Use production Pusher app credentials
2. **Process Manager:** Use supervisor or PM2 to keep queue workers running
3. **HTTPS:** Ensure SSL is enabled for Pusher connections
4. **Performance:** Monitor queue processing and database performance

### Common Issues & Solutions

**Messages not appearing in real-time:**
- ✅ Check queue workers are running: `php artisan queue:work`
- ✅ Verify Pusher credentials in `.env`
- ✅ Check browser console for JavaScript errors
- ✅ Ensure jobs table exists: `php artisan migrate`

**Chat page won't load:**
- ✅ Run: `npm run dev` or `npm run build`
- ✅ Check Laravel logs: `tail -f storage/logs/laravel.log`
- ✅ Verify users exist in database

**Real-time connections failing:**
- ✅ Check Pusher dashboard for connection activity
- ✅ Verify WebSocket connections in browser Network tab
- ✅ Test with different browsers

For detailed troubleshooting, see: [Real-Time Chat Debugging Guide](./documentation/real-time-chat-debugging-guide.md)

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

## Troubleshooting

### Common Issues and Solutions

#### Setup Issues

**1. Database Connection Errors**
```bash
# Error: "Access denied for user"
# Solution: Check MySQL credentials in .env
DB_USERNAME=root
DB_PASSWORD=your_actual_password

# Test connection
php artisan migrate:status
```

**2. Composer/NPM Installation Issues**
```bash
# Clear composer cache
composer clear-cache
composer install --no-cache

# Clear npm cache  
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

**3. Laravel Key Generation Issues**
```bash
# Generate new application key
php artisan key:generate

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

#### Chat System Issues

**1. Messages Not Appearing in Real-Time**

**Symptoms:** Messages save to database but don't appear instantly

**Solutions:**
```bash
# Check if queue worker is running
php artisan queue:work

# Verify Pusher configuration
php artisan config:show broadcasting

# Check Laravel logs
tail -f storage/logs/laravel.log

# Restart queue workers
php artisan queue:restart
php artisan queue:work
```

**2. "Table 'jobs' doesn't exist" Error**

**Solution:**
```bash
# Create jobs table
php artisan queue:table
php artisan migrate
```

**3. Pusher Connection Errors**

**Check browser console for errors like:**
- "Pusher connection failed"
- "WebSocket connection failed"

**Solutions:**
```bash
# Verify environment variables
echo $PUSHER_APP_KEY
echo $PUSHER_APP_CLUSTER

# Check .env file has correct values
cat .env | grep PUSHER

# Rebuild assets
npm run build
```

**4. CSRF Token Mismatch**

**Symptoms:** Chat form submissions fail with 419 errors

**Solution:**
```html
<!-- Ensure CSRF meta tag is in your layout -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```bash
# Clear sessions
php artisan session:table
php artisan migrate
```

#### Frontend Asset Issues

**1. CSS/JS Not Loading**
```bash
# Rebuild assets
npm run dev
# or for production
npm run build

# Check if Vite server is running
npm run dev
```

**2. Flowbite Components Not Working**
```bash
# Verify Flowbite is installed
npm list flowbite

# Check Tailwind config includes Flowbite
cat tailwind.config.js
```

#### Performance Issues

**1. Slow Database Queries**
```sql
-- Add indexes for chat queries
ALTER TABLE messages ADD INDEX idx_sender_receiver (sender_id, receiver_id);
ALTER TABLE messages ADD INDEX idx_created_at (created_at);
```

**2. Queue Processing Slow**
```bash
# Increase queue workers
php artisan queue:work --sleep=1 --tries=3

# Use Redis for better performance
composer require predis/predis
```

### Debug Commands

**System Health Check:**
```bash
# Check Laravel installation
php artisan --version

# Check database connection
php artisan migrate:status

# Check queue workers
php artisan queue:monitor

# Check broadcasting
php artisan route:list | grep chat
```

**Chat System Debug:**
```bash
# Check messages table
php artisan tinker
>>> App\Models\Message::count()
>>> App\Models\Message::latest()->first()

# Check users for testing
>>> App\Models\User::where('role', 'admin')->first()
```

**Logs and Monitoring:**
```bash
# Watch Laravel logs
tail -f storage/logs/laravel.log

# Watch queue logs
php artisan queue:work --verbose

# Check Pusher dashboard
# Go to your Pusher app dashboard to see real-time connection activity
```

### Getting Help

If you encounter issues not covered here:

1. **Check Documentation:**
   - [Chat System Implementation Guide](./documentation/chat-system-implementation.md)
   - [Real-Time Chat Debugging Guide](./documentation/real-time-chat-debugging-guide.md)

2. **Search Logs:**
   ```bash
   # Search for specific errors
   grep -r "ERROR" storage/logs/
   grep -r "MessageSent" storage/logs/
   ```

3. **Community Resources:**
   - [Laravel Documentation](https://laravel.com/docs)
   - [Pusher Documentation](https://pusher.com/docs)
   - [Laravel Echo Documentation](https://laravel.com/docs/broadcasting#client-side-installation)

4. **Create an Issue:**
   - Include error messages, logs, and steps to reproduce
   - Specify your environment (PHP version, OS, browser)

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
