
import { Loader2 } from 'lucide-react';

const LoadingScreen = () => {
  return (
    <div className="h-screen w-screen flex items-center justify-center bg-background">
      <div className="flex flex-col items-center space-y-4">
        <Loader2 className="h-12 w-12 text-primary animate-spin" />
        <p className="text-lg font-medium">Carregando...</p>
      </div>
    </div>
  );
};

export default LoadingScreen;
