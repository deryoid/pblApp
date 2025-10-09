# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Frontend Development
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Laravel Development
- `php artisan serve` - Start Laravel development server
- `php artisan test` - Run tests
- `php artisan config:clear` - Clear configuration cache
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Reset database and run seeders

### Full Development Stack
- `composer run dev` - Runs Laravel serve, queue listener, pail logs, and Vite dev server concurrently
- `composer run test` - Clear config and run tests

### Custom Commands
- `php artisan kunjungan:auto-tanggapan` - Mark old visits as "no response"
- `php artisan kunjungan:auto-tanggapan --dry-run` - Preview which visits would be updated without making changes

## Project Architecture

This is a Laravel-based Project-Based Learning (PBL) application with three user roles:

### User Roles & Authentication
- **Admin**: Manages users, periods, classes, groups, and evaluations
- **Evaluator**: Evaluates student projects and activities
- **Mahasiswa** (Student): Manages projects, activities, and company visits

### Core Domain Models
The application uses 21 Eloquent models primarily for:
- **Academic Structure**: `Periode`, `Kelas`, `Kelompok` (groups)
- **User Management**: `User`, with role-based middleware and UUID binding
- **Project Management**: `ProjectList`, `ProjectCard` for kanban-style boards
- **Evaluation**: `EvaluationSetting`, `EvaluationScore`, `EvaluationSession`
- **Company Visits**: `KunjunganMitra` (student company visits)

### Key Features

#### Project Management
- Kanban-style project boards for students (`proyek` and `aktivitas`)
- Drag-and-drop reordering with AJAX endpoints
- Project cards can be moved between lists and reordered
- Progress tracking and status management

#### Evaluation System
- Admin can configure evaluation settings and criteria
- Bulk scheduling of evaluation sessions
- Real-time evaluation scoring for projects
- Export capabilities for evaluation data

#### Company Visit Tracking
- Students can log company visits with details
- Auto-status updates for visits older than 2 days
- CSV import/export functionality for student data

### Database Configuration
- Uses SQLite by default (database/database.sqlite)
- Scheduled command for auto-updating visit status runs every minute
- All models use UUID for route model binding instead of auto-incrementing IDs
- Foreign key relationships are properly defined

### Frontend Stack
- **Bootstrap 5** for UI components
- **Tailwind CSS** for utility styling
- **Vite** for asset building and HMR
- **SweetAlert2** for notifications
- **Axios** for API calls

### Route Organization
Routes are organized by user role with middleware protection:
- `/admin/*` - Admin functionality
- `/evaluator/*` - Evaluator dashboard
- `/mahasiswa/*` - Student features
- UUID-based route model binding for most resources
- AJAX endpoints for drag-and-drop operations return JSON responses

### Important Patterns
- All controllers use UUID binding instead of auto-incrementing IDs
- AJAX endpoints for drag-and-drop operations return JSON responses
- Role-based middleware protects route groups
- Bulk operations support for admin tasks
- Real-time updates using Laravel's queue system
- Model auto-generation of UUIDs in boot methods
- Database migrations with proper foreign key constraints

### Authentication System
- Custom middleware for role-based access control (Admin, Evaluator, Mahasiswa)
- Login redirects based on user role
- Profile management for all users
- Password reset functionality for admin users

### Testing Framework
- PHPUnit for testing
- Feature tests for core functionality
- Database transactions for test isolation
- Model factories for test data generation