import api from './index'
import type { Branch } from '@/types'

interface NearestParams {
  lat: number
  lng: number
  radius?: number
}

export const fetchNearestBranches = async (params: NearestParams): Promise<Branch[]> => {
  const { data } = await api.get<{ data: Branch[] }>('/branches/nearest', { params })
  return data.data
}
