
export type UserRole = 'projetista' | 'montador' | 'admin';

export type UserType = {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  specialization?: 'torre' | 'embasamento' | 'ambos';
  createdAt: Date;
  updatedAt: Date;
};

export type ProjectStatus = 
  | 'em_dia'
  | 'atrasando'
  | 'atrasado'
  | 'parado'
  | 'enviado'
  | 'nao_iniciado'
  | 'em_execucao'
  | 'concluido';

export type ProjectStage = {
  name: string;
  status: ProjectStatus;
  updatedAt: Date;
  updatedBy: string;
};

export type TowerStages = {
  estrutura: ProjectStage;
  cobertura: ProjectStage;
  acabamentos: ProjectStage;
  internos: ProjectStage;
};

export type BasementStages = {
  estrutura: ProjectStage;
  laser: ProjectStage;
  mobiliario: ProjectStage;
  internos: ProjectStage;
  arborismo: ProjectStage;
};

export type DrawingApproval = {
  torre: ProjectStage;
  embasamento: ProjectStage;
};

export type CuttingApproval = {
  torre: ProjectStage;
  embasamento: ProjectStage;
  internos_torre: ProjectStage;
  internos_embasamento: ProjectStage;
};

export type Project = {
  id: string;
  name: string;
  deliveryDate: Date;
  createdAt: Date;
  updatedAt: Date;
  
  // Equipe designada
  projetistaTorre?: string;
  projetistaEmbasamento?: string;
  montadorTorre?: string;
  montadorEmbasamento?: string;
  
  // Status do projeto
  status: ProjectStatus;
  daysLate?: number;
  
  // Etapas de desenho
  drawingApproval: DrawingApproval;
  cuttingApproval: CuttingApproval;
  
  // Etapas de construção
  towerStages: TowerStages;
  basementStages: BasementStages;
  
  // Progresso geral
  progress: number;
};

export type StatusOption = {
  value: ProjectStatus;
  label: string;
  color: string;
};

export const STATUS_OPTIONS: StatusOption[] = [
  { value: 'em_dia', label: 'Em dia', color: 'bg-status-em-dia' },
  { value: 'atrasando', label: 'Atrasando', color: 'bg-status-atrasando' },
  { value: 'atrasado', label: 'Atrasado', color: 'bg-status-atrasado' },
  { value: 'parado', label: 'Parado', color: 'bg-status-parado' },
  { value: 'enviado', label: 'Enviado', color: 'bg-status-enviado' },
  { value: 'nao_iniciado', label: 'Não Iniciado', color: 'bg-status-nao-iniciado' },
  { value: 'em_execucao', label: 'Em Execução', color: 'bg-status-em-execucao' },
  { value: 'concluido', label: 'Concluído', color: 'bg-status-concluido' },
];
