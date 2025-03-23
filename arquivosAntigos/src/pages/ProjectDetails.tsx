
import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useData } from "@/contexts/DataContext";
import StatusBadge, { statusMap } from "@/components/StatusBadge";
import { ProjectStatus, STATUS_OPTIONS } from "@/types";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@/components/ui/tabs";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { format } from "date-fns";
import { cn } from "@/lib/utils";
import {
  ArrowLeft,
  Building2,
  Calendar as CalendarIcon,
  Check,
  ClipboardList,
  Edit,
  Save,
  Scissors,
} from "lucide-react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  HoverCard,
  HoverCardContent,
  HoverCardTrigger,
} from "@/components/ui/hover-card";

const ProjectDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { projects, users, getProject, getUser, updateProject, updateProjectStage } = useData();

  // Estado para o projeto
  const [project, setProject] = useState(getProject(id || ""));
  const [isEditing, setIsEditing] = useState(false);
  const [editedName, setEditedName] = useState("");
  const [editedDeliveryDate, setEditedDeliveryDate] = useState<Date | undefined>(
    undefined
  );
  const [selectedProjetistaTorre, setSelectedProjetistaTorre] = useState("");
  const [selectedProjetistaEmbasamento, setSelectedProjetistaEmbasamento] = useState("");
  const [selectedMontadorTorre, setSelectedMontadorTorre] = useState("");
  const [selectedMontadorEmbasamento, setSelectedMontadorEmbasamento] = useState("");

  // Filtrar usuários por função
  const projetistas = users.filter((user) => user.role === "projetista");
  const montadores = users.filter((user) => user.role === "montador");

  // Atualizar estado quando o projeto mudar
  useEffect(() => {
    const currentProject = getProject(id || "");
    setProject(currentProject);

    if (currentProject) {
      setEditedName(currentProject.name);
      setEditedDeliveryDate(currentProject.deliveryDate);
      setSelectedProjetistaTorre(currentProject.projetistaTorre || "");
      setSelectedProjetistaEmbasamento(currentProject.projetistaEmbasamento || "");
      setSelectedMontadorTorre(currentProject.montadorTorre || "");
      setSelectedMontadorEmbasamento(currentProject.montadorEmbasamento || "");
    }
  }, [id, projects, getProject]);

  // Se o projeto não existir, redirecionar para a lista
  useEffect(() => {
    if (!project && id !== "novo") {
      navigate("/projetos");
    }
  }, [project, navigate, id]);

  // Salvar alterações
  const saveChanges = () => {
    if (!project) return;

    updateProject(project.id, {
      name: editedName,
      deliveryDate: editedDeliveryDate || new Date(),
      projetistaTorre: selectedProjetistaTorre || undefined,
      projetistaEmbasamento: selectedProjetistaEmbasamento || undefined,
      montadorTorre: selectedMontadorTorre || undefined,
      montadorEmbasamento: selectedMontadorEmbasamento || undefined,
    });

    setIsEditing(false);
  };

  // Função para atualizar status de etapa
  const handleStageStatusChange = (
    stageType: "drawing" | "cutting" | "tower" | "basement",
    stageName: string,
    newStatus: ProjectStatus
  ) => {
    if (!project) return;
    
    // Usar ID do primeiro usuário para efeito de demonstração (em produção usaria o usuário logado)
    const currentUserId = users[0].id;
    
    updateProjectStage(project.id, stageType, stageName, newStatus, currentUserId);
  };

  // Obter nome do usuário
  const getUserName = (userId?: string) => {
    if (!userId) return "-";
    const user = getUser(userId);
    return user ? user.name : "-";
  };

  if (!project && id === "novo") {
    // Lógica para criação de novo projeto seria aqui
    return <div>Tela de criação de novo projeto</div>;
  }

  if (!project) return null;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button
          variant="outline"
          size="icon"
          onClick={() => navigate("/projetos")}
        >
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold flex items-center gap-2">
            {isEditing ? (
              <input
                type="text"
                value={editedName}
                onChange={(e) => setEditedName(e.target.value)}
                className="border-b border-primary/50 bg-transparent text-2xl font-bold focus:outline-none focus:border-primary"
              />
            ) : (
              project.name
            )}
          </h1>
          <div className="flex items-center gap-2 text-muted-foreground">
            <Building2 className="h-4 w-4" />
            <span>Projeto de Maquete</span>
          </div>
        </div>
        <div className="ml-auto flex items-center gap-2">
          {isEditing ? (
            <>
              <Button onClick={() => setIsEditing(false)} variant="outline">
                Cancelar
              </Button>
              <Button onClick={saveChanges} className="gap-1.5">
                <Save className="h-4 w-4" />
                Salvar
              </Button>
            </>
          ) : (
            <Button onClick={() => setIsEditing(true)} className="gap-1.5">
              <Edit className="h-4 w-4" />
              Editar
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Informações do Projeto */}
        <Card className="lg:col-span-1">
          <CardHeader>
            <CardTitle>Informações do Projeto</CardTitle>
            <CardDescription>Detalhes sobre o projeto de maquete</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Status</p>
              <div>
                <StatusBadge status={project.status} daysLate={project.daysLate} />
              </div>
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Data de Entrega</p>
              {isEditing ? (
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !editedDeliveryDate && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {editedDeliveryDate ? (
                        format(editedDeliveryDate, "dd/MM/yyyy")
                      ) : (
                        <span>Selecione uma data</span>
                      )}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={editedDeliveryDate}
                      onSelect={setEditedDeliveryDate}
                      initialFocus
                      className="pointer-events-auto"
                    />
                  </PopoverContent>
                </Popover>
              ) : (
                <p>{format(project.deliveryDate, "dd/MM/yyyy")}</p>
              )}
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Projetista Torre</p>
              {isEditing ? (
                <Select
                  value={selectedProjetistaTorre}
                  onValueChange={setSelectedProjetistaTorre}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione um projetista" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Nenhum</SelectItem>
                    {projetistas.map((user) => (
                      <SelectItem key={user.id} value={user.id}>
                        {user.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              ) : (
                <p>{getUserName(project.projetistaTorre)}</p>
              )}
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Projetista Embasamento</p>
              {isEditing ? (
                <Select
                  value={selectedProjetistaEmbasamento}
                  onValueChange={setSelectedProjetistaEmbasamento}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione um projetista" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Nenhum</SelectItem>
                    {projetistas.map((user) => (
                      <SelectItem key={user.id} value={user.id}>
                        {user.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              ) : (
                <p>{getUserName(project.projetistaEmbasamento)}</p>
              )}
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Montador Torre</p>
              {isEditing ? (
                <Select
                  value={selectedMontadorTorre}
                  onValueChange={setSelectedMontadorTorre}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione um montador" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Nenhum</SelectItem>
                    {montadores.map((user) => (
                      <SelectItem key={user.id} value={user.id}>
                        {user.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              ) : (
                <p>{getUserName(project.montadorTorre)}</p>
              )}
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Montador Embasamento</p>
              {isEditing ? (
                <Select
                  value={selectedMontadorEmbasamento}
                  onValueChange={setSelectedMontadorEmbasamento}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Selecione um montador" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Nenhum</SelectItem>
                    {montadores.map((user) => (
                      <SelectItem key={user.id} value={user.id}>
                        {user.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              ) : (
                <p>{getUserName(project.montadorEmbasamento)}</p>
              )}
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium text-muted-foreground">Progresso Geral</p>
              <div className="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  className="h-2.5 rounded-full transition-all duration-500 ease-in-out"
                  style={{
                    width: `${project.progress}%`,
                    backgroundColor:
                      project.status === "atrasado"
                        ? "#f87171"
                        : project.status === "atrasando"
                        ? "#fbbf24"
                        : "#4ade80",
                  }}
                ></div>
              </div>
              <p className="text-sm text-right">{project.progress}%</p>
            </div>
          </CardContent>
        </Card>

        {/* Tabs de Status */}
        <Card className="lg:col-span-2">
          <Tabs defaultValue="drawing">
            <CardHeader className="pb-0">
              <div className="flex justify-between items-center">
                <CardTitle>Status do Projeto</CardTitle>
                <TabsList>
                  <TabsTrigger value="drawing" className="gap-1.5">
                    <ClipboardList className="h-4 w-4" />
                    <span>Desenho</span>
                  </TabsTrigger>
                  <TabsTrigger value="cutting" className="gap-1.5">
                    <Scissors className="h-4 w-4" />
                    <span>Corte</span>
                  </TabsTrigger>
                  <TabsTrigger value="construction" className="gap-1.5">
                    <Building2 className="h-4 w-4" />
                    <span>Montagem</span>
                  </TabsTrigger>
                </TabsList>
              </div>
              <CardDescription>
                Acompanhe o status de todas as etapas do projeto
              </CardDescription>
            </CardHeader>
            <CardContent className="pt-6">
              <TabsContent value="drawing" className="m-0">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Fase</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow>
                      <TableCell>Envio para Avaliação - Torre</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={project.drawingApproval.torre.status}
                          onChange={(newStatus) =>
                            handleStageStatusChange("drawing", "torre", newStatus)
                          }
                        />
                      </TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Envio para Avaliação - Embasamento</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={project.drawingApproval.embasamento.status}
                          onChange={(newStatus) =>
                            handleStageStatusChange("drawing", "embasamento", newStatus)
                          }
                        />
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </TabsContent>

              <TabsContent value="cutting" className="m-0">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Fase</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow>
                      <TableCell>Liberação para Corte - Torre</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={project.cuttingApproval.torre.status}
                          onChange={(newStatus) =>
                            handleStageStatusChange("cutting", "torre", newStatus)
                          }
                        />
                      </TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Liberação para Corte - Embasamento</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={project.cuttingApproval.embasamento.status}
                          onChange={(newStatus) =>
                            handleStageStatusChange("cutting", "embasamento", newStatus)
                          }
                        />
                      </TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Liberação para Corte - Internos Torre</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={project.cuttingApproval.internos_torre.status}
                          onChange={(newStatus) =>
                            handleStageStatusChange("cutting", "internos_torre", newStatus)
                          }
                        />
                      </TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Liberação para Corte - Internos Embasamento</TableCell>
                      <TableCell>
                        <StatusDropdown
                          currentStatus={
                            project.cuttingApproval.internos_embasamento.status
                          }
                          onChange={(newStatus) =>
                            handleStageStatusChange(
                              "cutting",
                              "internos_embasamento",
                              newStatus
                            )
                          }
                        />
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </TabsContent>

              <TabsContent value="construction" className="m-0 space-y-6">
                <div>
                  <h3 className="text-lg font-semibold mb-2">Torre</h3>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Fase</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      <TableRow>
                        <TableCell>Estrutura</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.towerStages.estrutura.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("tower", "estrutura", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Cobertura</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.towerStages.cobertura.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("tower", "cobertura", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Acabamentos</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.towerStages.acabamentos.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("tower", "acabamentos", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Internos</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.towerStages.internos.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("tower", "internos", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>

                <div>
                  <h3 className="text-lg font-semibold mb-2">Embasamento</h3>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Fase</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      <TableRow>
                        <TableCell>Estrutura</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.basementStages.estrutura.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("basement", "estrutura", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Laser</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.basementStages.laser.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("basement", "laser", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Mobiliário</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.basementStages.mobiliario.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("basement", "mobiliario", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Internos</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.basementStages.internos.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("basement", "internos", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                      <TableRow>
                        <TableCell>Arborismo</TableCell>
                        <TableCell>
                          <StatusDropdown
                            currentStatus={project.basementStages.arborismo.status}
                            onChange={(newStatus) =>
                              handleStageStatusChange("basement", "arborismo", newStatus)
                            }
                          />
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>
              </TabsContent>
            </CardContent>
          </Tabs>
        </Card>
      </div>
    </div>
  );
};

// Componente de dropdown para status
interface StatusDropdownProps {
  currentStatus: ProjectStatus;
  onChange: (newStatus: ProjectStatus) => void;
}

const StatusDropdown = ({ currentStatus, onChange }: StatusDropdownProps) => {
  return (
    <Select value={currentStatus} onValueChange={onChange}>
      <SelectTrigger className="w-[180px]">
        <SelectValue>
          <div className="flex items-center gap-2">
            <div className={`w-2 h-2 rounded-full bg-status-${currentStatus.replace('_', '-')}`} />
            {statusMap[currentStatus]?.label || currentStatus}
          </div>
        </SelectValue>
      </SelectTrigger>
      <SelectContent>
        {STATUS_OPTIONS.map((option) => (
          <SelectItem key={option.value} value={option.value}>
            <div className="flex items-center gap-2">
              <div className={`w-2 h-2 rounded-full ${option.color}`} />
              {option.label}
            </div>
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
};

export default ProjectDetails;
