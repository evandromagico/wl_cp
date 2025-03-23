
import { ProjectStatus } from '@/types';
import { cn } from '@/lib/utils';

interface StatusBadgeProps {
  status: ProjectStatus;
  daysLate?: number;
  className?: string;
}

export const statusMap: Record<ProjectStatus, { label: string; className: string }> = {
  em_dia: { label: 'Em dia', className: 'status-badge-em-dia' },
  atrasando: { label: 'Atrasando', className: 'status-badge-atrasando' },
  atrasado: { label: 'Atrasado', className: 'status-badge-atrasado' },
  parado: { label: 'Parado', className: 'status-badge-parado' },
  enviado: { label: 'Enviado', className: 'status-badge-enviado' },
  nao_iniciado: { label: 'Não Iniciado', className: 'status-badge-nao-iniciado' },
  em_execucao: { label: 'Em Execução', className: 'status-badge-em-execucao' },
  concluido: { label: 'Concluído', className: 'status-badge-concluido' },
};

const StatusBadge = ({ status, daysLate, className }: StatusBadgeProps) => {
  const { label, className: badgeClass } = statusMap[status] || statusMap.nao_iniciado;

  return (
    <span className={cn('status-badge', badgeClass, className)}>
      {status === 'atrasado' && daysLate ? `${label} ${daysLate} dias` : label}
    </span>
  );
};

export default StatusBadge;
