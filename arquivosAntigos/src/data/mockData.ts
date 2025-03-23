import { UserType, Project, ProjectStatus } from '@/types';

export const mockUsers: UserType[] = [
  {
    id: '1',
    name: 'Julio',
    email: 'julio@maquetaria.com',
    role: 'projetista',
    specialization: 'torre',
    createdAt: new Date('2023-01-01'),
    updatedAt: new Date('2023-01-01')
  },
  {
    id: '2',
    name: 'Ana',
    email: 'ana@maquetaria.com',
    role: 'projetista',
    specialization: 'embasamento',
    createdAt: new Date('2023-01-02'),
    updatedAt: new Date('2023-01-02')
  },
  {
    id: '3',
    name: 'Douglas',
    email: 'douglas@maquetaria.com',
    role: 'montador',
    specialization: 'torre',
    createdAt: new Date('2023-01-03'),
    updatedAt: new Date('2023-01-03')
  },
  {
    id: '4',
    name: 'Ricardo',
    email: 'ricardo@maquetaria.com',
    role: 'montador',
    specialization: 'embasamento',
    createdAt: new Date('2023-01-04'),
    updatedAt: new Date('2023-01-04')
  },
  {
    id: '5',
    name: 'Jaqueline',
    email: 'jaqueline@maquetaria.com',
    role: 'projetista',
    specialization: 'ambos',
    createdAt: new Date('2023-01-05'),
    updatedAt: new Date('2023-01-05')
  },
  {
    id: '6',
    name: 'Willian',
    email: 'willian@maquetaria.com',
    role: 'projetista',
    specialization: 'torre',
    createdAt: new Date('2023-01-06'),
    updatedAt: new Date('2023-01-06')
  },
  {
    id: '7',
    name: 'Cesar',
    email: 'cesar@maquetaria.com',
    role: 'montador',
    specialization: 'ambos',
    createdAt: new Date('2023-01-07'),
    updatedAt: new Date('2023-01-07')
  },
  {
    id: '8',
    name: 'Admin',
    email: 'admin@maquetaria.com',
    role: 'admin',
    createdAt: new Date('2023-01-10'),
    updatedAt: new Date('2023-01-10')
  }
];

const createProjectStage = (status: ProjectStatus = 'nao_iniciado') => ({
  name: '',
  status,
  updatedAt: new Date(),
  updatedBy: ''
});

export const mockProjects: Project[] = [
  {
    id: '1',
    name: 'São João',
    deliveryDate: new Date('2025-02-28'),
    createdAt: new Date('2023-01-15'),
    updatedAt: new Date('2023-01-15'),
    projetistaTorre: '1', // Julio
    projetistaEmbasamento: '2', // Ana
    montadorTorre: '3', // Douglas
    montadorEmbasamento: '3', // Douglas
    status: 'em_dia',
    progress: 65,
    drawingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '1' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '1' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_torre: { name: 'Internos Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '1' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '2' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'concluido', updatedAt: new Date(), updatedBy: '3' },
      cobertura: { name: 'Cobertura', status: 'em_execucao', updatedAt: new Date(), updatedBy: '3' },
      acabamentos: { name: 'Acabamentos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'em_execucao', updatedAt: new Date(), updatedBy: '3' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'concluido', updatedAt: new Date(), updatedBy: '4' },
      laser: { name: 'Laser', status: 'concluido', updatedAt: new Date(), updatedBy: '4' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'concluido', updatedAt: new Date(), updatedBy: '4' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  },
  {
    id: '2',
    name: 'Inc 13',
    deliveryDate: new Date('2025-04-17'),
    createdAt: new Date('2023-02-10'),
    updatedAt: new Date('2023-02-10'),
    projetistaTorre: '1', // Julio
    projetistaEmbasamento: '1', // Julio
    montadorTorre: '4', // Ricardo
    montadorEmbasamento: '4', // Ricardo
    status: 'em_dia',
    progress: 40,
    drawingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '1' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_torre: { name: 'Internos Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '1' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      cobertura: { name: 'Cobertura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      acabamentos: { name: 'Acabamentos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'concluido', updatedAt: new Date(), updatedBy: '4' },
      laser: { name: 'Laser', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'concluido', updatedAt: new Date(), updatedBy: '4' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  },
  {
    id: '3',
    name: 'Authentic',
    deliveryDate: new Date('2025-05-10'),
    createdAt: new Date('2023-03-01'),
    updatedAt: new Date('2023-03-01'),
    projetistaTorre: '2', // Ana
    projetistaEmbasamento: '2', // Ana
    montadorTorre: '4', // Ricardo
    montadorEmbasamento: '4', // Ricardo
    status: 'em_dia',
    progress: 25,
    drawingApproval: {
      torre: { name: 'Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '2' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      embasamento: { name: 'Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '2' },
      internos_torre: { name: 'Internos Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '2' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      cobertura: { name: 'Cobertura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      acabamentos: { name: 'Acabamentos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'parado', updatedAt: new Date(), updatedBy: '4' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      laser: { name: 'Laser', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  },
  {
    id: '4',
    name: 'Legacy',
    deliveryDate: new Date('2025-05-10'),
    createdAt: new Date('2023-03-05'),
    updatedAt: new Date('2023-03-05'),
    projetistaTorre: '2', // Ana
    projetistaEmbasamento: '2', // Ana
    montadorTorre: '4', // Ricardo
    montadorEmbasamento: '4', // Ricardo
    status: 'em_dia',
    progress: 30,
    drawingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '2' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '2' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_torre: { name: 'Internos Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '2' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      cobertura: { name: 'Cobertura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      acabamentos: { name: 'Acabamentos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'em_execucao', updatedAt: new Date(), updatedBy: '4' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      laser: { name: 'Laser', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  },
  {
    id: '5',
    name: 'Félix',
    deliveryDate: new Date('2025-03-20'),
    createdAt: new Date('2023-03-10'),
    updatedAt: new Date('2023-03-10'),
    projetistaTorre: '2', // Ana
    projetistaEmbasamento: '2', // Ana
    montadorTorre: '4', // Ricardo
    montadorEmbasamento: '4', // Ricardo
    status: 'atrasando',
    progress: 15,
    drawingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '2' },
      embasamento: { name: 'Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '2' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      embasamento: { name: 'Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_torre: { name: 'Internos Torre', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      cobertura: { name: 'Cobertura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      acabamentos: { name: 'Acabamentos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      laser: { name: 'Laser', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  },
  {
    id: '6',
    name: 'Pedro Bittencourt',
    deliveryDate: new Date('2025-03-23'),
    createdAt: new Date('2023-03-15'),
    updatedAt: new Date('2023-03-15'),
    projetistaTorre: '5', // Jaqueline
    projetistaEmbasamento: '5', // Jaqueline
    montadorTorre: '7', // Cesar
    montadorEmbasamento: '7', // Cesar
    status: 'em_dia',
    progress: 85,
    drawingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '5' },
      embasamento: { name: 'Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '5' }
    },
    cuttingApproval: {
      torre: { name: 'Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '5' },
      embasamento: { name: 'Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '5' },
      internos_torre: { name: 'Internos Torre', status: 'enviado', updatedAt: new Date(), updatedBy: '5' },
      internos_embasamento: { name: 'Internos Embasamento', status: 'enviado', updatedAt: new Date(), updatedBy: '5' }
    },
    towerStages: {
      estrutura: { name: 'Estrutura', status: 'concluido', updatedAt: new Date(), updatedBy: '7' },
      cobertura: { name: 'Cobertura', status: 'concluido', updatedAt: new Date(), updatedBy: '7' },
      acabamentos: { name: 'Acabamentos', status: 'em_execucao', updatedAt: new Date(), updatedBy: '7' },
      internos: { name: 'Internos', status: 'em_execucao', updatedAt: new Date(), updatedBy: '7' }
    },
    basementStages: {
      estrutura: { name: 'Estrutura', status: 'em_execucao', updatedAt: new Date(), updatedBy: '7' },
      laser: { name: 'Laser', status: 'em_execucao', updatedAt: new Date(), updatedBy: '7' },
      mobiliario: { name: 'Mobiliário', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      internos: { name: 'Internos', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' },
      arborismo: { name: 'Arborismo', status: 'nao_iniciado', updatedAt: new Date(), updatedBy: '' }
    }
  }
];
