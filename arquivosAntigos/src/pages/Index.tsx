
import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import LoadingScreen from "@/components/LoadingScreen";

const Index = () => {
  const navigate = useNavigate();

  useEffect(() => {
    // Redirecionamento assíncrono para dar tempo ao carregamento inicial
    const redirectTimer = setTimeout(() => {
      navigate("/dashboard");
    }, 100);
    
    return () => clearTimeout(redirectTimer);
  }, [navigate]);

  return <LoadingScreen />;
};

export default Index;
