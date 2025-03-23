
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useData } from '@/contexts/DataContext';
import StatusBadge from '@/components/StatusBadge';
import { PieChart, Pie, Cell } from 'recharts';
import { Card } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  ArrowUp, 
  ArrowDown, 
  Clock, 
  BarChart, 
  PieChart as PieChartIcon,
  Building,
  CheckCircle
} from 'lucide-react';

const Dashboard = () => {
  const { projects, loading } = useData();
  const [activeTab, setActiveTab] = useState('overview');
  const navigate = useNavigate();

  // Estatísticas
  const totalProjects = projects.length;
  const completedProjects = projects.filter(p => p.progress === 100).length;
  const inProgressProjects = projects.filter(p => p.progress > 0 && p.progress < 100).length;
  const notStartedProjects = projects.filter(p => p.progress === 0).length;
  
  const onTimeProjects = projects.filter(p => p.status === 'em_dia').length;
  const delayingProjects = projects.filter(p => p.status === 'atrasando').length;
  const lateProjects = projects.filter(p => p.status === 'atrasado').length;

  // Classificar projetos por progresso
  const sortedProjects = [...projects].sort((a, b) => b.progress - a.progress);

  // Dados para o gráfico de pizza com cores mais claras
  const progressChartData = [
    { name: 'Concluído', value: completedProjects, fill: '#10b981' },
    { name: 'Em Progresso', value: inProgressProjects, fill: '#3b82f6' },
    { name: 'Não Iniciado', value: notStartedProjects, fill: '#e5e7eb' },
  ];

  // Dados para o gráfico de status com cores mais claras
  const statusChartData = [
    { name: 'Em Dia', value: onTimeProjects, fill: '#4ade80' },
    { name: 'Atrasando', value: delayingProjects, fill: '#fbbf24' },
    { name: 'Atrasado', value: lateProjects, fill: '#f87171' },
  ];

  const renderCustomizedLabel = ({ name, percent }: any) => `${name}: ${(percent * 100).toFixed(0)}%`;

  // Função para navegar para a página de detalhes do projeto
  const handleProjectClick = (projectId: string) => {
    navigate(`/projetos/${projectId}`);
  };

  return (
    <div className="space-y-8">
      <div className="space-y-1">
        <h1 className="text-2xl font-bold">Dashboard</h1>
        <p className="text-muted-foreground">Acompanhe o progresso de todos os projetos em um só lugar.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card className="p-6 flex flex-col space-y-4 hover-lift">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-muted-foreground">Total de Projetos</p>
              <p className="text-3xl font-bold">{totalProjects}</p>
            </div>
            <div className="p-2 bg-primary/10 rounded-full">
              <Building className="h-6 w-6 text-primary" />
            </div>
          </div>
        </Card>

        <Card className="p-6 flex flex-col space-y-4 hover-lift">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-muted-foreground">Projetos Em Dia</p>
              <p className="text-3xl font-bold text-status-em-dia">{onTimeProjects}</p>
            </div>
            <div className="p-2 bg-green-100 rounded-full">
              <CheckCircle className="h-6 w-6 text-status-em-dia" />
            </div>
          </div>
          <p className="text-xs text-muted-foreground">{Math.round((onTimeProjects / totalProjects) * 100)}% dos projetos</p>
        </Card>

        <Card className="p-6 flex flex-col space-y-4 hover-lift">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-muted-foreground">Projetos Atrasando</p>
              <p className="text-3xl font-bold text-status-atrasando">{delayingProjects}</p>
            </div>
            <div className="p-2 bg-yellow-100 rounded-full">
              <Clock className="h-6 w-6 text-status-atrasando" />
            </div>
          </div>
          <p className="text-xs text-muted-foreground">{Math.round((delayingProjects / totalProjects) * 100)}% dos projetos</p>
        </Card>

        <Card className="p-6 flex flex-col space-y-4 hover-lift">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-muted-foreground">Projetos Atrasados</p>
              <p className="text-3xl font-bold text-status-atrasado">{lateProjects}</p>
            </div>
            <div className="p-2 bg-red-100 rounded-full">
              <ArrowDown className="h-6 w-6 text-status-atrasado" />
            </div>
          </div>
          <p className="text-xs text-muted-foreground">{Math.round((lateProjects / totalProjects) * 100)}% dos projetos</p>
        </Card>
      </div>

      <Tabs defaultValue="overview" className="w-full" onValueChange={setActiveTab}>
        <TabsList>
          <TabsTrigger value="overview" className="flex items-center gap-1.5">
            <PieChartIcon className="h-4 w-4" />
            <span>Visão Geral</span>
          </TabsTrigger>
          <TabsTrigger value="progress" className="flex items-center gap-1.5">
            <BarChart className="h-4 w-4" />
            <span>Progresso de Projetos</span>
          </TabsTrigger>
        </TabsList>
        
        <TabsContent value="overview">
          <div className="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card className="p-6">
              <h3 className="text-lg font-semibold mb-4">Status dos Projetos</h3>
              <div className="h-[300px] flex items-center justify-center">
                <PieChart width={300} height={300}>
                  <defs>
                    {statusChartData.map((entry, index) => (
                      <linearGradient key={`gradient-${index}`} id={`gradient-${entry.name}`} x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor={entry.fill} stopOpacity={0.8}/>
                        <stop offset="100%" stopColor={entry.fill} stopOpacity={1}/>
                      </linearGradient>
                    ))}
                  </defs>
                  <Pie
                    data={statusChartData}
                    dataKey="value"
                    nameKey="name"
                    cx="50%"
                    cy="50%"
                    outerRadius={100}
                    fill="#8884d8"
                    label={renderCustomizedLabel}
                    labelLine={false}
                  >
                    {statusChartData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={`url(#gradient-${entry.name})`} />
                    ))}
                  </Pie>
                </PieChart>
              </div>
            </Card>

            <Card className="p-6">
              <h3 className="text-lg font-semibold mb-4">Progresso dos Projetos</h3>
              <div className="h-[300px] flex items-center justify-center">
                <PieChart width={300} height={300}>
                  <defs>
                    {progressChartData.map((entry, index) => (
                      <linearGradient key={`progress-gradient-${index}`} id={`progress-gradient-${entry.name}`} x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor={entry.fill} stopOpacity={0.8}/>
                        <stop offset="100%" stopColor={entry.fill} stopOpacity={1}/>
                      </linearGradient>
                    ))}
                  </defs>
                  <Pie
                    data={progressChartData}
                    dataKey="value"
                    nameKey="name"
                    cx="50%"
                    cy="50%"
                    outerRadius={100}
                    fill="#8884d8"
                    label={renderCustomizedLabel}
                    labelLine={false}
                  >
                    {progressChartData.map((entry, index) => (
                      <Cell key={`progress-cell-${index}`} fill={`url(#progress-gradient-${entry.name})`} />
                    ))}
                  </Pie>
                </PieChart>
              </div>
            </Card>
          </div>
        </TabsContent>
        
        <TabsContent value="progress">
          <div className="mt-6">
            <Card className="p-6">
              <h3 className="text-lg font-semibold mb-4">Progresso por Projeto</h3>
              <div className="space-y-6">
                {sortedProjects.map(project => (
                  <div 
                    key={project.id} 
                    className="space-y-2 p-3 hover:bg-gray-50 rounded-md cursor-pointer transition-colors"
                    onClick={() => handleProjectClick(project.id)}
                  >
                    <div className="flex justify-between items-center">
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <h4 className="font-medium">{project.name}</h4>
                          <StatusBadge status={project.status} daysLate={project.daysLate} />
                        </div>
                        <p className="text-sm text-muted-foreground">
                          Entrega: {project.deliveryDate.toLocaleDateString()}
                        </p>
                      </div>
                      <p className="font-semibold">{project.progress}%</p>
                    </div>
                    <div className="h-2 bg-gray-200 rounded overflow-hidden">
                      <div 
                        className="h-full transition-all duration-500 ease-in-out"
                        style={{ 
                          width: `${project.progress}%`,
                          backgroundColor: 
                            project.status === 'atrasado' ? '#f87171' : 
                            project.status === 'atrasando' ? '#fbbf24' : 
                            '#4ade80'
                        }}
                      />
                    </div>
                  </div>
                ))}
              </div>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default Dashboard;
