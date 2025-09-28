# MedX360 Frontend Integration Guide

This guide explains how to integrate the React frontend with your WordPress backend and deploy the complete MedX360 system.

## 🚀 Quick Start

### 1. **Install Dependencies**
```bash
cd wp-content/plugins/medx360/frontend
npm install
```

### 2. **Configure Environment**
Create `.env` file in the frontend directory:
```env
REACT_APP_WP_URL=http://localhost:3000
REACT_APP_WP_NONCE=your-wordpress-nonce-here
```

### 3. **Start Development**
```bash
npm start
```

## 🔧 WordPress Integration

### **Method 1: WordPress Admin Integration**

Add this to your WordPress plugin's admin page:

```php
// In your WordPress plugin admin page
function medx360_admin_page() {
    // Enqueue React app
    wp_enqueue_script('medx360-react', plugin_dir_url(__FILE__) . 'frontend/build/static/js/main.js', array(), '1.0.0', true);
    wp_enqueue_style('medx360-react', plugin_dir_url(__FILE__) . 'frontend/build/static/css/main.css', array(), '1.0.0');
    
    // Pass WordPress data to React
    wp_localize_script('medx360-react', 'wpApiSettings', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'user' => wp_get_current_user(),
    ));
    
    echo '<div id="medx360-root"></div>';
}
```

### **Method 2: Standalone Integration**

Create a WordPress page template:

```php
<?php
/*
Template Name: MedX360 Frontend
*/

get_header();

// Enqueue React app
wp_enqueue_script('medx360-react', get_template_directory_uri() . '/medx360/build/static/js/main.js', array(), '1.0.0', true);
wp_enqueue_style('medx360-react', get_template_directory_uri() . '/medx360/build/static/css/main.css', array(), '1.0.0');

// Pass WordPress data to React
wp_localize_script('medx360-react', 'wpApiSettings', array(
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'user' => wp_get_current_user(),
));
?>

<div id="medx360-root"></div>

<?php get_footer(); ?>
```

## 🏗️ Build Process

### **Development Build**
```bash
npm start
```
- Runs on `http://localhost:3000`
- Hot reloading enabled
- Development tools available

### **Production Build**
```bash
npm run build
```
- Creates optimized `build/` folder
- Minified JavaScript and CSS
- Production-ready files

### **Build Output Structure**
```
build/
├── static/
│   ├── css/
│   │   └── main.[hash].css
│   ├── js/
│   │   ├── main.[hash].js
│   │   └── [chunk].[hash].js
│   └── media/
│       └── [images]
├── index.html
└── manifest.json
```

## 🔐 Authentication Setup

### **WordPress Nonce Authentication**

The frontend uses WordPress nonce authentication. Ensure your WordPress setup includes:

```php
// In your WordPress theme's functions.php or plugin
function medx360_enqueue_scripts() {
    if (is_page('medx360') || is_admin()) {
        wp_localize_script('medx360-react', 'wpApiSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'user' => wp_get_current_user(),
        ));
    }
}
add_action('wp_enqueue_scripts', 'medx360_enqueue_scripts');
```

### **API Permissions**

Ensure your WordPress user has the required capabilities:
- `manage_options` - Full access
- `edit_posts` - Manage clinics, doctors, bookings
- `read` - View data

## 🌐 Deployment Options

### **Option 1: WordPress Plugin Integration**

1. **Build the React app**
   ```bash
   npm run build
   ```

2. **Copy build files to plugin directory**
   ```bash
   cp -r build/* wp-content/plugins/medx360/assets/frontend/
   ```

3. **Update plugin to serve React files**
   ```php
   // In your plugin's main file
   function medx360_enqueue_frontend() {
       wp_enqueue_script('medx360-react', plugin_dir_url(__FILE__) . 'assets/frontend/static/js/main.js', array(), '1.0.0', true);
       wp_enqueue_style('medx360-react', plugin_dir_url(__FILE__) . 'assets/frontend/static/css/main.css', array(), '1.0.0');
   }
   ```

### **Option 2: Separate Web Server**

1. **Build and deploy React app**
   ```bash
   npm run build
   # Upload build/ folder to your web server
   ```

2. **Configure CORS in WordPress**
   ```php
   // Add to WordPress functions.php
   function medx360_cors_headers() {
       header('Access-Control-Allow-Origin: https://your-react-domain.com');
       header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
       header('Access-Control-Allow-Headers: Content-Type, X-WP-Nonce');
   }
   add_action('rest_api_init', 'medx360_cors_headers');
   ```

### **Option 3: CDN Deployment**

1. **Build and upload to CDN**
   ```bash
   npm run build
   # Upload to AWS S3, Cloudflare, etc.
   ```

2. **Update WordPress to load from CDN**
   ```php
   wp_enqueue_script('medx360-react', 'https://your-cdn.com/medx360/main.js', array(), '1.0.0', true);
   ```

## 🔧 Configuration

### **Environment Variables**

Create `.env` files for different environments:

**Development (.env.development)**
```env
REACT_APP_WP_URL=http://localhost:3000
REACT_APP_WP_NONCE=dev-nonce
REACT_APP_ENV=development
```

**Production (.env.production)**
```env
REACT_APP_WP_URL=https://yourdomain.com
REACT_APP_WP_NONCE=prod-nonce
REACT_APP_ENV=production
```

### **API Configuration**

Update API base URL in `src/services/api.ts`:

```typescript
// For WordPress subdirectory installation
this.baseURL = process.env.REACT_APP_WP_URL || window.location.origin + '/wp';

// For WordPress root installation
this.baseURL = process.env.REACT_APP_WP_URL || window.location.origin;
```

## 🚀 Performance Optimization

### **Code Splitting**
The app automatically splits code by routes:
- Dashboard loads separately
- Each page loads independently
- Reduces initial bundle size

### **Caching Strategy**
- React Query caches API responses
- Browser caches static assets
- Service worker for offline support

### **Bundle Analysis**
```bash
npm install --save-dev webpack-bundle-analyzer
npm run build
npx webpack-bundle-analyzer build/static/js/*.js
```

## 🐛 Troubleshooting

### **Common Issues**

1. **CORS Errors**
   ```php
   // Add to WordPress functions.php
   add_action('rest_api_init', function() {
       remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
       add_filter('rest_pre_serve_request', function($value) {
           header('Access-Control-Allow-Origin: *');
           header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
           header('Access-Control-Allow-Headers: Content-Type, X-WP-Nonce');
           return $value;
       });
   });
   ```

2. **Nonce Authentication Issues**
   - Ensure nonce is passed correctly
   - Check user permissions
   - Verify REST API is enabled

3. **Build Issues**
   - Clear npm cache: `npm cache clean --force`
   - Delete node_modules and reinstall
   - Check Node.js version compatibility

4. **Routing Issues**
   - Configure web server for SPA routing
   - Add .htaccess rules for Apache
   - Configure nginx for React Router

### **Debug Mode**

Enable debug mode in development:

```typescript
// In src/services/api.ts
if (process.env.NODE_ENV === 'development') {
    console.log('API Request:', config);
}
```

## 📱 Mobile Optimization

### **Progressive Web App (PWA)**
The app includes PWA features:
- Offline support
- App-like experience
- Push notifications (future)
- Install prompts

### **Responsive Design**
- Mobile-first approach
- Touch-friendly interfaces
- Optimized layouts for all screen sizes

## 🔒 Security Considerations

### **API Security**
- WordPress nonce authentication
- User capability checks
- Input validation and sanitization
- HTTPS enforcement

### **Frontend Security**
- XSS protection
- CSRF protection via nonce
- Secure headers
- Content Security Policy

## 📊 Monitoring and Analytics

### **Error Tracking**
Consider integrating:
- Sentry for error tracking
- Google Analytics for usage analytics
- Performance monitoring tools

### **Health Checks**
```typescript
// Add to your API service
async healthCheck() {
    try {
        const response = await this.api.get('/onboarding/status');
        return response.status === 200;
    } catch (error) {
        return false;
    }
}
```

## 🎯 Next Steps

1. **Customize the UI** - Modify components to match your brand
2. **Add Features** - Implement additional medical management features
3. **Integrate Payments** - Add Stripe/PayPal integration
4. **Mobile App** - Consider React Native for mobile apps
5. **Analytics** - Add usage tracking and reporting

## 📞 Support

For technical support:
- Check the troubleshooting section
- Review WordPress and React documentation
- Contact the development team

---

**Your MedX360 system is now ready to manage medical bookings efficiently!** 🏥✨
