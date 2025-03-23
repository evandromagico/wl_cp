
import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Users, Building2, PieChart, Settings, Menu, X } from 'lucide-react';
import { cn } from '@/lib/utils';

interface MainLayoutProps {
  children: React.ReactNode;
}

const MainLayout = ({ children }: MainLayoutProps) => {
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const navigate = useNavigate();
  const location = useLocation();

  // Fechar a barra lateral em telas pequenas
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth < 1024) {
        setIsSidebarOpen(false);
      } else {
        setIsSidebarOpen(true);
      }
    };

    handleResize(); // Verificar tamanho inicial
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const navItems = [
    { 
      path: '/', 
      label: 'Dashboard', 
      icon: <PieChart className="w-5 h-5" />
    },
    { 
      path: '/projetos', 
      label: 'Projetos', 
      icon: <Building2 className="w-5 h-5" /> 
    },
    { 
      path: '/usuarios', 
      label: 'Usuários', 
      icon: <Users className="w-5 h-5" /> 
    },
    { 
      path: '/configuracoes', 
      label: 'Configurações', 
      icon: <Settings className="w-5 h-5" /> 
    }
  ];

  return (
    <div className="h-screen flex overflow-hidden bg-gray-50 dark:bg-gray-900">
      {/* Sidebar Backdrop for mobile */}
      {!isSidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/30 lg:hidden z-20"
          onClick={() => setIsSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed lg:relative inset-y-0 left-0 z-30 w-64 flex-shrink-0 overflow-y-auto bg-white dark:bg-gray-800 shadow-lg transition-transform duration-300 ease-in-out transform",
          isSidebarOpen ? "translate-x-0" : "-translate-x-full lg:translate-x-0"
        )}
      >
        <div className="flex flex-col h-full">
          {/* Logo and close button */}
          <div className="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <h1 className="text-xl font-bold dark:text-white">WL Maquetes</h1>
            <button
              className="lg:hidden p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
              onClick={() => setIsSidebarOpen(false)}
            >
              <X className="w-5 h-5 dark:text-gray-300" />
            </button>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1">
            {navItems.map((item) => (
              <button
                key={item.path}
                onClick={() => {
                  navigate(item.path);
                  if (window.innerWidth < 1024) {
                    setIsSidebarOpen(false);
                  }
                }}
                className={cn(
                  "nav-item w-full dark:text-gray-300 dark:hover:bg-gray-700",
                  location.pathname === item.path && "nav-item-active bg-primary/10 dark:bg-gray-700"
                )}
              >
                {item.icon}
                <span>{item.label}</span>
              </button>
            ))}
          </nav>

          {/* Footer */}
          <div className="p-4 border-t dark:border-gray-700">
            <p className="text-xs text-gray-500 dark:text-gray-400">
              © 2024 WL Maquetes
            </p>
          </div>
        </div>
      </aside>

      {/* Main content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Header */}
        <header className="bg-white dark:bg-gray-800 shadow-sm z-10">
          <div className="flex items-center justify-between p-4">
            <button
              className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden"
              onClick={() => setIsSidebarOpen(true)}
            >
              <Menu className="w-5 h-5 dark:text-gray-300" />
            </button>
            <div className="flex-1 px-4">
              <h2 className="text-lg font-semibold dark:text-white">
                {navItems.find(item => item.path === location.pathname)?.label || 'WL Maquetes'}
              </h2>
            </div>
            <div className="flex items-center space-x-3">
              <div className="w-8 h-8 rounded-full bg-primary/10 dark:bg-gray-700 flex items-center justify-center text-primary dark:text-white font-medium">
                A
              </div>
            </div>
          </div>
        </header>

        {/* Main content area */}
        <main className="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900">
          <div className="animate-fade-in">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};

export default MainLayout;
