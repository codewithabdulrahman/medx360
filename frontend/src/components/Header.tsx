import React from 'react';
import { Menu, Bell, User, Search } from 'lucide-react';
import { useApp, appActions } from '../contexts/AppContext';

const Header: React.FC = () => {
  const { state, dispatch } = useApp();

  const toggleSidebar = () => {
    dispatch(appActions.toggleSidebar());
  };

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Left side */}
          <div className="flex items-center">
            <button
              onClick={toggleSidebar}
              className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 lg:hidden"
            >
              <Menu className="h-5 w-5" />
            </button>
            
            <div className="hidden lg:block ml-4">
              <h2 className="text-xl font-semibold text-gray-900">
                {getPageTitle(state.currentClinic?.name)}
              </h2>
            </div>
          </div>

          {/* Center - Search */}
          <div className="flex-1 max-w-lg mx-4 hidden md:block">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-5 w-5 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Search..."
                className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>

          {/* Right side */}
          <div className="flex items-center space-x-4">
            {/* Notifications */}
            <button className="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-md">
              <Bell className="h-5 w-5" />
            </button>

            {/* User menu */}
            <div className="flex items-center space-x-3">
              <div className="hidden sm:block">
                <p className="text-sm font-medium text-gray-900">
                  {state.user.user?.display_name || 'Admin User'}
                </p>
                <p className="text-xs text-gray-500">
                  {state.currentClinic?.name || 'No Clinic Selected'}
                </p>
              </div>
              
              <button className="flex-shrink-0 p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-md">
                <User className="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

// Helper function to get page title based on current context
const getPageTitle = (clinicName?: string): string => {
  if (clinicName) {
    return `${clinicName} - MedX360`;
  }
  return 'MedX360 Medical Management';
};

export default Header;
