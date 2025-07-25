import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    console.log('API Request:', config.method?.toUpperCase(), config.url, config.data)
    return config
  },
  (error) => {
    console.error('API Request Error:', error)
    return Promise.reject(error)
  }
)

api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.status, response.config.url, response.data)
    return response
  },
  (error) => {
    console.error('API Response Error:', error)
    console.error('Error status:', error.response?.status)
    console.error('Error data:', error.response?.data)
    console.error('Error config:', error.config)
    
    if (error.response?.status === 401) {
      console.log('Token expirado, redirecionando para login')
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
