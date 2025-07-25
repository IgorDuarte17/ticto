export const POSITIONS = [
  { value: '', label: 'Todos os cargos' },
  { value: 'Desenvolvedor', label: 'Desenvolvedor' },
  { value: 'Analista', label: 'Analista' },
  { value: 'Designer', label: 'Designer' },
  { value: 'Gerente', label: 'Gerente' },
  { value: 'Coordenador', label: 'Coordenador' },
  { value: 'Assistente', label: 'Assistente' },
  { value: 'Estagiário', label: 'Estagiário' },
  { value: 'Consultor', label: 'Consultor' },
]

export const FORM_POSITIONS = POSITIONS.filter(p => p.value !== '')

export const FILTER_POSITIONS = POSITIONS
