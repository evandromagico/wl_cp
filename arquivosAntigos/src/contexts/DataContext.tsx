
import React, { createContext, useContext, useState, useEffect } from 'react';
import { Project, UserType, ProjectStatus } from '@/types';
import { format, differenceInDays } from 'date-fns';
import { toast } from '@/hooks/use-toast';

// Mock de dados iniciais
import { mockUsers, mockProjects } from '@/data/mockData';

interface DataContextType {
  users: UserType[];
  projects: Project[];
  getUser: (id: string) => UserType | undefined;
  getProject: (id: string) => Project | undefined;
  addUser: (user: Omit<UserType, 'id' | 'createdAt' | 'updatedAt'>) => void;
  updateUser: (id: string, userData: Partial<UserType>) => void;
  deleteUser: (id: string) => void;
  addProject: (project: Omit<Project, 'id' | 'createdAt' | 'updatedAt' | 'status' | 'daysLate' | 'progress'>) => void;
  updateProject: (id: string, projectData: Partial<Project>) => void;
  deleteProject: (id: string) => void;
  updateProjectStatus: (id: string, newStatus: ProjectStatus) => void;
  updateProjectStage: (
    projectId: string,
    stageType: 'drawing' | 'cutting' | 'tower' | 'basement',
    stageName: string,
    status: ProjectStatus,
    userId: string
  ) => void;
  calculateDeliveryStatus: (deliveryDate: Date) => { status: ProjectStatus; daysLate?: number };
  loading: boolean;
}

const DataContext = createContext<DataContextType | undefined>(undefined);

export const DataProvider = ({ children }: { children: React.ReactNode }) => {
  const [users, setUsers] = useState<UserType[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const initData = () => {
      try {
        setUsers(mockUsers);
        
        const updatedProjects = mockProjects.map(project => {
          const deliveryStatus = calculateDeliveryStatus(project.deliveryDate);
          return {
            ...project,
            status: deliveryStatus.status,
            daysLate: deliveryStatus.daysLate
          };
        });
        
        setProjects(updatedProjects);
        setLoading(false);
      } catch (error) {
        console.error('Erro ao inicializar dados:', error);
        toast({
          title: "Erro ao carregar dados",
          variant: "destructive"
        });
        setLoading(false);
      }
    };

    initData();
  }, []);

  const calculateDeliveryStatus = (deliveryDate: Date) => {
    const today = new Date();
    const diffDays = differenceInDays(deliveryDate, today);

    if (diffDays > 3) {
      return { status: 'em_dia' as ProjectStatus };
    }
    if (diffDays > 0) {
      return { status: 'atrasando' as ProjectStatus };
    }
    if (diffDays === 0) {
      return { status: 'atrasado' as ProjectStatus };
    }
    return { 
      status: 'atrasado' as ProjectStatus, 
      daysLate: Math.abs(diffDays) 
    };
  };

  const getUser = (id: string) => users.find(user => user.id === id);

  const addUser = (userData: Omit<UserType, 'id' | 'createdAt' | 'updatedAt'>) => {
    const newUser: UserType = {
      ...userData,
      id: crypto.randomUUID(),
      createdAt: new Date(),
      updatedAt: new Date(),
    };
    
    setUsers(prev => [...prev, newUser]);
    toast({
      title: "Usuário adicionado com sucesso",
      variant: "default"
    });
  };

  const updateUser = (id: string, userData: Partial<UserType>) => {
    setUsers(prev => prev.map(user => 
      user.id === id 
        ? { ...user, ...userData, updatedAt: new Date() } 
        : user
    ));
    toast({
      title: "Usuário atualizado com sucesso",
      variant: "default" 
    });
  };

  const deleteUser = (id: string) => {
    setUsers(prev => prev.filter(user => user.id !== id));
    toast({
      title: "Usuário removido com sucesso",
      variant: "default"
    });
  };

  const getProject = (id: string) => projects.find(project => project.id === id);

  const addProject = (projectData: Omit<Project, 'id' | 'createdAt' | 'updatedAt' | 'status' | 'daysLate' | 'progress'>) => {
    const deliveryStatus = calculateDeliveryStatus(projectData.deliveryDate);
    
    const newProject: Project = {
      ...projectData,
      id: crypto.randomUUID(),
      createdAt: new Date(),
      updatedAt: new Date(),
      status: deliveryStatus.status,
      daysLate: deliveryStatus.daysLate,
      progress: 0
    };
    
    setProjects(prev => [...prev, newProject]);
    toast({
      title: "Projeto adicionado com sucesso",
      variant: "default"
    });
  };

  const updateProject = (id: string, projectData: Partial<Project>) => {
    setProjects(prev => prev.map(project => {
      if (project.id === id) {
        let status = project.status;
        let daysLate = project.daysLate;
        
        if (projectData.deliveryDate) {
          const deliveryStatus = calculateDeliveryStatus(projectData.deliveryDate);
          status = deliveryStatus.status;
          daysLate = deliveryStatus.daysLate;
        }
        
        return { 
          ...project, 
          ...projectData, 
          status,
          daysLate,
          updatedAt: new Date() 
        };
      }
      return project;
    }));
    
    toast({
      title: "Projeto atualizado com sucesso",
      variant: "default"
    });
  };

  const deleteProject = (id: string) => {
    setProjects(prev => prev.filter(project => project.id !== id));
    toast({
      title: "Projeto removido com sucesso",
      variant: "default"
    });
  };

  const updateProjectStatus = (id: string, newStatus: ProjectStatus) => {
    setProjects(prev => prev.map(project => 
      project.id === id 
        ? { ...project, status: newStatus, updatedAt: new Date() } 
        : project
    ));
  };

  const updateProjectStage = (
    projectId: string, 
    stageType: 'drawing' | 'cutting' | 'tower' | 'basement',
    stageName: string, 
    status: ProjectStatus,
    userId: string
  ) => {
    setProjects(prev => prev.map(project => {
      if (project.id === projectId) {
        const updatedProject = { ...project, updatedAt: new Date() };
        
        const newStage = {
          name: stageName,
          status,
          updatedAt: new Date(),
          updatedBy: userId
        };
        
        if (stageType === 'drawing') {
          updatedProject.drawingApproval = {
            ...updatedProject.drawingApproval,
            [stageName as keyof typeof updatedProject.drawingApproval]: newStage
          };
        } else if (stageType === 'cutting') {
          updatedProject.cuttingApproval = {
            ...updatedProject.cuttingApproval,
            [stageName as keyof typeof updatedProject.cuttingApproval]: newStage
          };
        } else if (stageType === 'tower') {
          updatedProject.towerStages = {
            ...updatedProject.towerStages,
            [stageName as keyof typeof updatedProject.towerStages]: newStage
          };
        } else if (stageType === 'basement') {
          updatedProject.basementStages = {
            ...updatedProject.basementStages,
            [stageName as keyof typeof updatedProject.basementStages]: newStage
          };
        }
        
        const allStages = [
          ...Object.values(updatedProject.drawingApproval),
          ...Object.values(updatedProject.cuttingApproval),
          ...Object.values(updatedProject.towerStages),
          ...Object.values(updatedProject.basementStages)
        ];
        
        const completedCount = allStages.filter(stage => 
          stage.status === 'concluido' || stage.status === 'enviado'
        ).length;
        
        updatedProject.progress = Math.round((completedCount / allStages.length) * 100);
        
        return updatedProject;
      }
      return project;
    }));
    
    toast({
      title: "Status atualizado com sucesso",
      variant: "default"
    });
  };

  const value = {
    users,
    projects,
    getUser,
    getProject,
    addUser,
    updateUser,
    deleteUser,
    addProject,
    updateProject,
    deleteProject,
    updateProjectStatus,
    updateProjectStage,
    calculateDeliveryStatus,
    loading
  };

  return <DataContext.Provider value={value}>{children}</DataContext.Provider>;
};

export const useData = () => {
  const context = useContext(DataContext);
  if (context === undefined) {
    throw new Error('useData must be used within a DataProvider');
  }
  return context;
};
