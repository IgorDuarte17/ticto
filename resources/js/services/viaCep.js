import api from './api'

export const viaCepService = {
  async getAddressByCep(cep) {
    try {
      const cleanCep = cep.replace(/\D/g, '')
      
      if (cleanCep.length !== 8) {
        throw new Error('CEP deve conter 8 dígitos')
      }
      
      const response = await api.get(`/via-cep/${cleanCep}`)
      
      if (response.data.status === 'success') {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro ao consultar CEP')
      }
    } catch (error) {
      if (error.response?.data?.message) {
        throw new Error(error.response.data.message)
      }
      throw new Error(error.message || 'Erro ao consultar CEP')
    }
  },

  formatCep(cep) {
    const cleaned = cep.replace(/\D/g, '')
    if (cleaned.length === 8) {
      return cleaned.replace(/(\d{5})(\d{3})/, '$1-$2')
    }
    return cep
  },

  isValidCep(cep) {
    const cleaned = cep.replace(/\D/g, '')
    return cleaned.length === 8
  }
}

export default viaCepService
