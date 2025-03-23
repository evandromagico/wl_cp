
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useData } from '@/contexts/DataContext';
import { UserRole, UserType } from '@/types';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
  Filter,
  Plus,
  Search,
  MoreHorizontal,
  ChevronDown,
  Edit,
  Trash2,
  User,
  UserCog,
  HardHat,
} from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { format } from 'date-fns';

const roleIcons: Record<UserRole, React.ReactNode> = {
  admin: <UserCog className="h-4 w-4 mr-2" />,
  projetista: <User className="h-4 w-4 mr-2" />,
  montador: <HardHat className="h-4 w-4 mr-2" />,
};

const roleLabels: Record<UserRole, string> = {
  admin: 'Administrador',
  projetista: 'Projetista',
  montador: 'Montador',
};

const UsersList = () => {
  const { users, deleteUser } = useData();
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [roleFilter, setRoleFilter] = useState<UserRole | 'all'>('all');
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [userToDelete, setUserToDelete] = useState<UserType | null>(null);

  // Filtrar usuários
  const filteredUsers = users.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         user.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesRole = roleFilter === 'all' || user.role === roleFilter;
    return matchesSearch && matchesRole;
  });

  // Formatar data de criação
  const formatDate = (date: Date) => {
    return format(date, 'dd/MM/yyyy');
  };

  // Função para iniciar exclusão
  const handleDeleteClick = (user: UserType) => {
    setUserToDelete(user);
    setIsDeleteDialogOpen(true);
  };

  // Confirmar exclusão
  const confirmDelete = () => {
    if (userToDelete) {
      deleteUser(userToDelete.id);
      setIsDeleteDialogOpen(false);
      setUserToDelete(null);
    }
  };

  // Função para renderizar especialização
  const renderSpecialization = (specialization?: string) => {
    if (!specialization) return '-';
    
    const specializationMap: Record<string, string> = {
      'torre': 'Torre',
      'embasamento': 'Embasamento',
      'ambos': 'Torre e Embasamento'
    };
    
    return specializationMap[specialization] || specialization;
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
        <div>
          <h1 className="text-2xl font-bold">Usuários</h1>
          <p className="text-muted-foreground">Gerenciar projetistas e montadores.</p>
        </div>
        <Button onClick={() => navigate('/usuarios/novo')} className="gap-2">
          <Plus className="h-4 w-4" />
          Novo Usuário
        </Button>
      </div>

      <Card>
        <CardHeader className="py-5">
          <CardTitle>Lista de Usuários</CardTitle>
          <CardDescription>
            Visualize e gerencie todos os usuários do sistema.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="mb-4 flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar usuários..."
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
                <DropdownMenuItem onClick={() => setRoleFilter('all')}>
                  Todos os Cargos
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => setRoleFilter('admin')}>
                  {roleIcons.admin}
                  Administrador
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => setRoleFilter('projetista')}>
                  {roleIcons.projetista}
                  Projetista
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => setRoleFilter('montador')}>
                  {roleIcons.montador}
                  Montador
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <div className="border rounded-md">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nome</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Cargo</TableHead>
                  <TableHead>Especialização</TableHead>
                  <TableHead>Data de Cadastro</TableHead>
                  <TableHead className="w-[80px]"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredUsers.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center py-6 text-muted-foreground">
                      Nenhum usuário encontrado.
                    </TableCell>
                  </TableRow>
                ) : (
                  filteredUsers.map((user) => (
                    <TableRow key={user.id} className="hover:bg-muted/30 cursor-pointer">
                      <TableCell 
                        className="font-medium"
                        onClick={() => navigate(`/usuarios/${user.id}`)}
                      >
                        {user.name}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/usuarios/${user.id}`)}>
                        {user.email}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/usuarios/${user.id}`)}>
                        <Badge variant="outline" className="flex w-fit items-center gap-1">
                          {roleIcons[user.role]}
                          {roleLabels[user.role]}
                        </Badge>
                      </TableCell>
                      <TableCell onClick={() => navigate(`/usuarios/${user.id}`)}>
                        {renderSpecialization(user.specialization)}
                      </TableCell>
                      <TableCell onClick={() => navigate(`/usuarios/${user.id}`)}>
                        {formatDate(user.createdAt)}
                      </TableCell>
                      <TableCell>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => navigate(`/usuarios/${user.id}`)}>
                              <Edit className="h-4 w-4 mr-2" />
                              Editar
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                              onClick={() => handleDeleteClick(user)}
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
            Tem certeza que deseja excluir o usuário <span className="font-semibold">{userToDelete?.name}</span>? 
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

export default UsersList;
