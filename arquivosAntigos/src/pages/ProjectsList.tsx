
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useData } from '@/contexts/DataContext';
import StatusBadge from '@/components/StatusBadge';
import { Project, ProjectStatus, STATUS_OPTIONS } from '@/types';
import { Button } from '@/components/ui/button';
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/components/ui/table';
import { 
  DropdownMenu, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { 
  Card, 
  CardContent,
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { 
  Filter, 
  Plus, 
  Search, 
  MoreHorizontal, 
  ChevronDown, 
  Edit, 
  Trash2 
} from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { format } from 'date-fns';

const ProjectsList = () => {
  const { projects, users, getUser, deleteProject } = useData();
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<ProjectStatus | 'all'>('all');
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [projectToDelete, setProjectToDelete] = useState<Project | null>(null);

  // Filtrar projetos
  const filteredProjects = projects.filter(project => {
    const matchesSearch = project.name.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === 'all' || project.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  // Função para obter nome do usuário
  const getUserName = (userId?: string) => {
    if (!userId) return '-';
    const user = getUser(userId);
    return user ? user.name : '-';
  };

  // Formatar data de entrega
  const formatDate = (date: Date) => {
    return format(date, 'dd/MM/yyyy');
  };

  // Função para iniciar exclusão
  const handleDeleteClick = (project: Project) => {
    setProjectToDelete(project);
    setIsDeleteDialogOpen(true);
  };

  // Confirmar exclusão
  const confirmDelete = () => {
    if (projectToDelete) {
      deleteProject(projectToDelete.id);
      setIsDeleteDialogOpen(false);
      setProjectToDelete(null);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
        <div>
          <h1 className="text-2xl font-bold">Projetos</h1>
          <p className="text-muted-foreground">Gerenciar todos os projetos da maquetaria.</p>
        </div>
        <Button onClick={() => navigate('/projetos/novo')} className="gap-2">
          <Plus className="h-4 w-4" />
          Novo Projeto
        </Button>
      </div>

      <Card>
        <CardHeader className="py-5">
          <CardTitle>Lista de Projetos</CardTitle>
          <CardDescription>
            Visualize e gerencie todos os projetos da maquetaria.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="mb-4 flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar projetos..."
                className="pl-8"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" className="gap-1.5">
                  <Filter className="h-4 w-4" />
                  Filtrar
                  <ChevronDown className="h-3 w-3 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-[200px]">
                <DropdownMenuItem onClick={() => setStatusFilter('all')}>
                  Todos os Status
                </DropdownMenuItem>
                {STATUS_OPTIONS.map((option) => (
                  <DropdownMenuItem 
                    key={option.value}
                    onClick={() => setStatusFilter(option.value)}
                    className="flex items-center gap-2"
                  >
                    <div className={`w-2 h-2 rounded-full ${option.color}`} />
                    {option.label}
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <div className="border rounded-md">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Projeto</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Projetista Torre</TableHead>
                  <TableHead>Projetista Embasamento</TableHead>
                  <TableHead>Data de Entrega</TableHead>
                  <TableHead>Progresso</TableHead>
                  <TableHead className="w-[80px]"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredProjects.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} className="text-center py-6 text-muted-foreground">
                      Nenhum projeto encontrado.
                    </TableCell>
                  </TableRow>
                ) : (
                  filteredProjects.map((project) => (
                    <TableRow key={project.id} className="hover:bg-muted/30 cursor-pointer">
                      <TableCell 
                        className="font-medium"
                        onClick={() => navigate(`/projetos/${project.id}`)}
                      >
                        {project.name}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/projetos/${project.id}`)}>
                        <StatusBadge status={project.status} daysLate={project.daysLate} />
                      </TableCell>
                      <TableCell onClick={() => navigate(`/projetos/${project.id}`)}>
                        {getUserName(project.projetistaTorre)}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/projetos/${project.id}`)}>
                        {getUserName(project.projetistaEmbasamento)}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/projetos/${project.id}`)}>
                        {formatDate(project.deliveryDate)}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/projetos/${project.id}`)}>
                        <div className="flex items-center gap-2">
                          <div className="w-full bg-gray-200 rounded-full h-2.5">
                            <div 
                              className="h-2.5 rounded-full transition-all duration-500 ease-in-out"
                              style={{ 
                                width: `${project.progress}%`,
                                backgroundColor: 
                                  project.status === 'atrasado' ? '#f87171' : 
                                  project.status === 'atrasando' ? '#fbbf24' : 
                                  '#4ade80'
                              }}
                            ></div>
                          </div>
                          <span className="text-xs font-medium">{project.progress}%</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => navigate(`/projetos/${project.id}`)}>
                              <Edit className="h-4 w-4 mr-2" />
                              Editar
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                              onClick={() => handleDeleteClick(project)}
                              className="text-destructive focus:text-destructive"
                            >
                              <Trash2 className="h-4 w-4 mr-2" />
                              Excluir
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Diálogo de confirmação de exclusão */}
      <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirmar exclusão</DialogTitle>
          </DialogHeader>
          <p>
            Tem certeza que deseja excluir o projeto <span className="font-semibold">{projectToDelete?.name}</span>? 
            Esta ação não pode ser desfeita.
          </p>
          <div className="flex justify-end space-x-2 mt-4">
            <Button variant="outline" onClick={() => setIsDeleteDialogOpen(false)}>
              Cancelar
            </Button>
            <Button variant="destructive" onClick={confirmDelete}>
              Excluir
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ProjectsList;
