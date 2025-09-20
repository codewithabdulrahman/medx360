# MedX360 Plugin - Installation Guide

## Quick Start

### 1. Prerequisites
- WordPress 5.0 or higher
- PHP 8.0 or higher
- Node.js 16 or higher
- MySQL 5.7 or higher

### 2. Installation Steps

#### Step 1: Install Dependencies
```bash
cd wp-content/plugins/medx360
npm install
composer install
```

#### Step 2: Build React Applications
```bash
# Build both admin and frontend applications
npm run build:all

# Or use the build script
./build.sh
```

#### Step 3: Activate Plugin
1. Go to WordPress Admin → Plugins
2. Find "MedX360"
3. Click "Activate"

#### Step 4: Configure Plugin
1. Go to WordPress Admin → MedX360 → Settings
2. Configure your settings:
   - General settings (timezone, date format)
   - Booking settings (advance booking days, cancellation policy)
   - Notification settings (email, SMS)
   - Security settings (HIPAA compliance)

### 3. Usage

#### Admin Interface
- Navigate to "MedX360" in WordPress admin
- Use the React-powered dashboard to manage:
  - Patients
  - Healthcare providers
  - Appointments
  - Clinical notes
  - Prescriptions

#### Frontend Booking
Add the booking form to any page:
```
[medx360_booking]
```

Or use the Gutenberg block:
1. Add a new block
2. Search for "MedX360"
3. Select the booking form block

### 4. Development

#### Development Mode
```bash
# Start development server for admin
npm run dev -- --mode admin

# Start development server for frontend
npm run dev -- --mode frontend
```

#### Code Quality
```bash
# Run linting
npm run lint

# Run type checking
npm run type-check

# Run tests
npm run test
```

### 5. Troubleshooting

#### Common Issues

**Build fails:**
- Ensure Node.js 16+ is installed
- Run `npm install` to install dependencies
- Check for TypeScript errors with `npm run type-check`

**Plugin not loading:**
- Check PHP error logs
- Ensure all dependencies are installed
- Verify WordPress version compatibility

**React app not rendering:**
- Ensure build files exist in `dist/` directory
- Check browser console for JavaScript errors
- Verify API endpoints are accessible

#### Getting Help
- Check the README.md for detailed documentation
- Review the plugin settings in WordPress admin
- Check browser console for JavaScript errors
- Review PHP error logs for server-side issues

### 6. Security Considerations

- Ensure HTTPS is enabled for production
- Configure HIPAA compliance settings
- Set up proper user roles and permissions
- Regular security updates and backups

### 7. Performance Optimization

- Enable caching for better performance
- Optimize images and assets
- Use a CDN for static assets
- Monitor database performance

## Next Steps

After installation:
1. Configure your healthcare providers
2. Set up services and locations
3. Create patient registration forms
4. Configure notification templates
5. Test the booking flow
6. Train your staff on the admin interface
