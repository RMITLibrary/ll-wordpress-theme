# RMIT Learning Lab WordPress Theme

WordPress theme development repository for the RMIT Learning Lab website.

## Prerequisites

- Node.js v20+ (uses `.nvmrc` for version management)
- npm or yarn
- Local WordPress development environment (e.g., Local by Flywheel, MAMP, XAMPP)
- PHP 7.4+ (WordPress requirement)
- MySQL 5.7+ or MariaDB 10.3+

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/ll-wordpress-theme.git
cd ll-wordpress-theme
```

### 2. Set Up Node Environment

```bash
# If you have nvm installed, use the correct Node version
nvm use

# Install dependencies
npm install
```

### 3. Set Up WordPress

1. Install WordPress 6.8.2 in your local environment
2. Copy the theme files to your WordPress installation:
   ```bash
   cp -r wp-content/themes/* /path/to/wordpress/wp-content/themes/
   ```
3. Activate the "RMIT Learning Lab" theme in WordPress admin

### 4. Development

```bash
# Watch and compile SASS during development
npm run dev

# Build production CSS (minified)
npm run build

# One-time compile with expanded output
npm run sass:dev
```

## Available Scripts

- `npm run dev` - Watch SASS files and auto-compile on changes
- `npm run build` - Build compressed CSS with source maps
- `npm run sass` - Single compile with compressed output and source maps
- `npm run clean` - Remove generated CSS files

## Project Structure

```
ll-wordpress-theme/
├── wp-content/
│   └── themes/
│       ├── picostrap5/          # Parent theme (Bootstrap 5 base)
│       └── rmit-learning-lab/   # Child theme (main development)
│           ├── sass/             # SASS source files
│           │   ├── main.scss     # Main SASS entry point
│           │   ├── design-system/# Design system components
│           │   └── learning-lab/ # Site-specific styles
│           ├── css-output/       # Compiled CSS output
│           │   └── bundle.css    # Main compiled CSS
│           ├── js/               # JavaScript files
│           ├── page-templates/   # Custom page templates
│           ├── custom-shortcodes/# Custom WordPress shortcodes
│           ├── acf-json/         # ACF field definitions
│           └── functions.php     # Theme functions
├── package.json                  # Node dependencies and scripts
├── .nvmrc                        # Node version specification
└── README.md                     # This file
```

## Theme Architecture

This is a WordPress child theme setup:
- **Parent Theme**: Picostrap5 (v3.1.0) - Provides Bootstrap 5.3.3 foundation
- **Child Theme**: RMIT Learning Lab (v3.1.0) - Custom implementation

## SASS Workflow

1. Edit SASS files in `wp-content/themes/rmit-learning-lab/sass/`
2. Main entry point is `sass/main.scss`
3. Custom styles go in `sass/_custom.scss`
4. Compiled output goes to `css-output/bundle.css` (compressed with source maps)
5. All builds produce consistent compressed CSS matching the parent theme compiler

## Version Control

The repository tracks:
- Child theme files (rmit-learning-lab)
- Parent theme files (picostrap5)
- Development configuration (package.json, .nvmrc)

The repository ignores:
- WordPress core files
- Plugin files
- Upload directories
- Build artifacts and source maps
- Local configuration files

## Local Development Setup

### Recommended Tools

1. **Laravel Herd** - Modern PHP/MySQL local development
2. **Local by Flywheel** - Simple WordPress local development
3. **MAMP/XAMPP** - Traditional PHP/MySQL stack

### Database Configuration

1. Create a local database
2. Configure `wp-config.php` with your local database credentials
3. Never commit `wp-config.php` to version control

## Contributing

1. Create a feature branch
2. Make your changes
3. Test locally
4. Submit a pull request

## Support

For issues or questions, contact the Digital Learning Team at RMIT University.

## License

GPL-2.0 (WordPress compatible)