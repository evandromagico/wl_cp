
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { DataProvider } from "./contexts/DataContext";
import MainLayout from "./components/MainLayout";
import Dashboard from "./pages/Dashboard";
import ProjectsList from "./pages/ProjectsList";
import ProjectDetails from "./pages/ProjectDetails";
import UsersList from "./pages/UsersList";
import Settings from "./pages/Settings";
import NotFound from "./pages/NotFound";
import LoadingScreen from "./components/LoadingScreen";
import { useState, useEffect } from "react";

const queryClient = new QueryClient();

const App = () => {
  const [isLoading, setIsLoading] = useState(true);

  // Simulação de carregamento inicial
  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1500);
    return () => clearTimeout(timer);
  }, []);

  if (isLoading) {
    return <LoadingScreen />;
  }

  return (
    <QueryClientProvider client={queryClient}>
      <DataProvider>
        <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <MainLayout>
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/projetos" element={<ProjectsList />} />
                <Route path="/projetos/:id" element={<ProjectDetails />} />
                <Route path="/usuarios" element={<UsersList />} />
                <Route path="/configuracoes" element={<Settings />} />
                <Route path="*" element={<NotFound />} />
              </Routes>
            </MainLayout>
          </BrowserRouter>
        </TooltipProvider>
      </DataProvider>
    </QueryClientProvider>
  );
};

export default App;
