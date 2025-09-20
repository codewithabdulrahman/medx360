# Medx360 React WordPress Plugin

A comprehensive React-based WordPress plugin implementing all 14 epics for the Medx360 booking system.

## 🚀 Features

### 🆓 **Free Features (Epics 1-8)**
- **Epic 1:** Core Booking System (Calendar, Forms, Lists)
- **Epic 2:** Patient Management (Directory, Profiles, Forms)
- **Epic 3:** Payment & Billing (Transactions, Dashboard)
- **Epic 4:** Staff Management (Directory, Scheduling)
- **Epic 5:** Notifications (Center, Settings)
- **Epic 6:** Reporting (Dashboard, Analytics)
- **Epic 7:** Roles & Permissions (Management, Settings)
- **Epic 8:** UI/UX Enhancements (Settings, Profile)

### 💎 **Premium Features (Epics 9-14)**
- **Epic 9:** Multi-Location Management
- **Epic 10:** Advanced Staff & Resource Management
- **Epic 11:** Advanced Notifications
- **Epic 12:** Integrations
- **Epic 13:** Advanced Payments
- **Epic 14:** Advanced Reporting & Analytics

## 🛠️ Setup

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Development Options:**

   **Option A: WordPress Development (Recommended)**
   ```bash
   npm run build:watch
   ```
   - Automatically rebuilds on file changes
   - Works with WordPress admin interface
   - Refresh WordPress page to see changes

   **Option B: Standalone Development**
   ```bash
   npm run dev
   ```
   - Opens standalone React app at `http://localhost:3000`
   - Hot reload works here
   - Good for component testing

3. **Production build:**
   ```bash
   npm run build
   ```

## 📦 WordPress Installation

1. Upload the entire `medx360` folder to your WordPress `wp-content/plugins/` directory
2. Activate the plugin in your WordPress admin panel
3. Navigate to the "Medx360" menu item in the admin sidebar
4. The React app will load with full navigation and all epic features

## 🏗️ Architecture

### Component Structure
```
src/components/
├── App.jsx                 # Main app with React Router
├── Layout/                 # Navigation & Layout
├── Dashboard/              # Main dashboard
├── Booking/                # Epic 1: Core Booking
├── Patients/               # Epic 2: Patient Management
├── Payments/               # Epic 3: Payment & Billing
├── Staff/                  # Epic 4: Staff Management
├── Notifications/          # Epic 5: Notifications
├── Reports/                # Epic 6: Reporting
├── Roles/                  # Epic 7: Roles & Permissions
├── Settings/               # Epic 8: UI/UX Enhancements
├── Profile/                # Epic 8: User Profile
└── Paid/                   # Epics 9-14: Premium Features
```

### Routing
- **Dashboard:** `/dashboard`
- **Booking:** `/booking`, `/booking/new`, `/booking/list`
- **Patients:** `/patients`, `/patients/new`, `/patients/:id`
- **Payments:** `/payments`, `/payments/new`, `/billing`
- **Staff:** `/staff`, `/staff/new`, `/staff/schedule`
- **Notifications:** `/notifications`, `/notifications/settings`
- **Reports:** `/reports`, `/reports/appointments`, `/reports/financial`
- **Roles:** `/roles`, `/permissions`
- **Settings:** `/settings`, `/profile`
- **Premium:** `/multi-location`, `/advanced-staff`, etc.

## 🎨 UI Features

- **WordPress Admin Compatible:** Seamlessly integrates with WordPress admin interface
- **Modern Design:** Clean, professional interface matching WordPress styling
- **Perfect Alignment:** Properly aligned components with consistent spacing
- **Responsive Layout:** Mobile-friendly navigation tabs and grids
- **Epic Organization:** Clear separation of free vs premium features
- **Premium Banners:** Special styling for paid features
- **Tab Navigation:** WordPress-style navigation tabs for easy access
- **Consistent Typography:** Unified font sizes, weights, and colors

## 🔧 Development

### Development Workflow

1. **For WordPress Development:**
   ```bash
   npm run build:watch
   ```
   - Edit files in `src/components/`
   - Changes automatically rebuild
   - Refresh WordPress admin page to see changes

2. **For Component Testing:**
   ```bash
   npm run dev
   ```
   - Opens standalone React app
   - Hot reload works here
   - Test components outside WordPress

### File Structure
- **Edit Components:** Modify React components in `src/components/`
- **Build Output:** Compiled files go to `build/` folder
- **WordPress Integration:** Plugin loads files from `build/` folder

### Troubleshooting
- **Changes not showing:** Use `npm run build:watch` and refresh WordPress page
- **Dev server issues:** Check if port 3000 is available
- **Build errors:** Check terminal for error messages

## 📋 Requirements

- **Node.js:** 18.8.0 or higher
- **WordPress:** 5.0 or higher
- **Browser:** Modern browser with JavaScript enabled
- **Dependencies:** React 19, React Router DOM, Vite 4.5

## 📚 Documentation

See `EPIC_STRUCTURE.md` for detailed epic implementation structure and component organization.

## 🚀 Next Steps

1. **Implement Core Logic:** Add actual functionality to each component
2. **API Integration:** Connect to WordPress REST API
3. **State Management:** Add Redux/Zustand for complex state
4. **Testing:** Add unit and integration tests
5. **Premium Features:** Implement actual paid feature logic
