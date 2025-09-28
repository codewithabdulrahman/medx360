# MedX360 React Frontend

This is the React frontend for the MedX360 WordPress plugin. It provides a modern, responsive interface for managing medical bookings, clinics, hospitals, doctors, and more.

## Features

- **Modern React 18** with hooks and functional components
- **React Router** for client-side routing
- **React Query** for efficient data fetching and caching
- **Tailwind CSS** for styling with custom design system
- **Webpack 5** for bundling and development server
- **Hot Module Replacement** for fast development
- **Responsive Design** that works on all devices
- **WordPress Integration** via REST API

## Quick Start

### Prerequisites

- Node.js 16+ 
- npm 8+
- WordPress with MedX360 plugin installed

### Installation

1. Navigate to the frontend directory:
```bash
cd wp-content/plugins/medx360/frontend
```

2. Install dependencies:
```bash
npm install
```

### Development Commands

#### Start Development Server
```bash
npm run dev
```
- Starts webpack dev server on `http://localhost:3000`
- Hot module replacement enabled
- Source maps for debugging
- Watches for file changes

#### Build for Development
```bash
npm run build:dev
```
- Creates development build in `dist/` folder
- Includes source maps
- Faster build time

#### Watch Mode
```bash
npm run watch
```
- Builds files and watches for changes
- Rebuilds automatically on file changes
- No dev server (for production-like testing)

### Production Commands

#### Build for Production
```bash
npm run build
```
- Creates optimized production build
- Minifies JavaScript and CSS
- Generates content hashes for caching
- Removes development code

#### Clean Build Directory
```bash
npm run clean
```
- Removes the `dist/` directory
- Use before fresh builds

## Project Structure

```
frontend/
├── src/
│   ├── components/          # Reusable React components
│   │   ├── Layout.js       # Main layout wrapper
│   │   ├── Sidebar.js      # Navigation sidebar
│   │   └── Header.js       # Top header bar
│   ├── pages/              # Page components
│   │   ├── Dashboard.js    # Main dashboard
│   │   ├── Onboarding.js   # Setup wizard
│   │   └── index.js        # Page exports
│   ├── hooks/              # Custom React hooks
│   │   └── useApi.js       # API integration hooks
│   ├── services/           # API services
│   │   └── apiService.js   # Axios-based API client
│   ├── App.js              # Main app component
│   ├── index.js            # App entry point
│   ├── index.css           # Global styles
│   └── index.html          # HTML template
├── dist/                   # Built files (generated)
├── package.json            # Dependencies and scripts
├── webpack.config.js       # Webpack configuration
├── tailwind.config.js     # Tailwind CSS config
└── postcss.config.js      # PostCSS configuration
```

## Development Workflow

### 1. Start Development
```bash
npm run dev
```
This will:
- Start webpack dev server on port 3000
- Enable hot module replacement
- Watch for file changes
- Provide source maps for debugging

### 2. Make Changes
- Edit files in `src/` directory
- Changes will automatically reload in browser
- Use React Developer Tools for debugging

### 3. Test Production Build
```bash
npm run build
```
This creates optimized files in `dist/` folder that WordPress will serve.

### 4. Deploy
- Run `npm run build` to create production files
- The `dist/` folder contains the built frontend
- WordPress plugin will automatically serve these files

## WordPress Integration

The React frontend integrates with WordPress through:

1. **REST API**: All data operations use WordPress REST API endpoints
2. **Authentication**: Uses WordPress nonce authentication
3. **User Context**: Receives current user data from WordPress
4. **Admin Interface**: Loads within WordPress admin area

### API Endpoints

The frontend communicates with these WordPress REST API endpoints:

- `GET /wp-json/medx360/v1/clinics` - List clinics
- `POST /wp-json/medx360/v1/clinics` - Create clinic
- `GET /wp-json/medx360/v1/hospitals` - List hospitals
- `GET /wp-json/medx360/v1/doctors` - List doctors
- `GET /wp-json/medx360/v1/bookings` - List bookings
- `GET /wp-json/medx360/v1/onboarding/status` - Setup status
- And many more...

## Styling

The project uses **Tailwind CSS** with a custom design system:

- **Primary Colors**: Blue palette for main actions
- **Secondary Colors**: Green palette for success states
- **Typography**: Inter font family
- **Components**: Pre-built component classes
- **Responsive**: Mobile-first design approach

### Custom CSS Classes

```css
.btn-primary     /* Primary button styling */
.btn-secondary   /* Secondary button styling */
.card           /* Card container */
.form-input     /* Form input styling */
.table          /* Table styling */
.badge-success  /* Success badge */
```

## State Management

The app uses **React Query** for server state management:

- **Caching**: Automatic caching of API responses
- **Background Updates**: Refetch data when needed
- **Optimistic Updates**: Update UI before server response
- **Error Handling**: Centralized error management

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### Common Issues

1. **Build fails**: Check Node.js version (16+ required)
2. **Hot reload not working**: Restart dev server
3. **API errors**: Check WordPress plugin is active
4. **Styling issues**: Clear browser cache

### Debug Mode

Enable debug mode by setting `NODE_ENV=development`:
```bash
NODE_ENV=development npm run dev
```

## Contributing

1. Make changes in `src/` directory
2. Test with `npm run dev`
3. Build with `npm run build`
4. Test production build in WordPress

## License

GPL v2 or later (same as WordPress)
