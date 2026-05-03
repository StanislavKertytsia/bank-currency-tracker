export interface Bank {
  id: number
  name: string
  logo_url: string | null
  rating: number | null
  phone: string | null
  email: string | null
  description: string | null
  website: string | null
  address: string | null
  slug: string
}

export interface BankDetail extends Bank {
  rates: ExchangeRate[]
  branches: Branch[]
}

export interface Currency {
  id: number
  code: string
  name: string
}

export interface ExchangeRate {
  id: number
  bank_id: number
  currency_id: number
  buy_rate: number
  sell_rate: number
  source: string
  updated_at: string
  bank?: Bank
  currency?: Currency
}

export interface NbuRate {
  currency_code: string
  currency_name: string
  rate: number
  avg_buy_rate: number | null
  avg_sell_rate: number | null
}

export interface Branch {
  id: number
  bank_id: number
  branch_name: string
  address: string
  phone: string | null
  lat: number
  lng: number
  distance?: number
  bank?: Bank
}

export interface RateHistory {
  id: number
  bank_id: number
  currency_id: number
  buy_rate: number
  sell_rate: number
  previous_buy_rate: number
  previous_sell_rate: number
  change_percent: number
  source: string
  recorded_at: string
  bank?: Bank
  currency?: Currency
}

export interface Statistic {
  recorded_at: string
  buy_rate: number
  sell_rate: number
  currency_id: number
  currency_code: string
  bank_id: number
  bank_name?: string
  min_buy_rate?: number
  max_buy_rate?: number
}

export interface User {
  id: number
  name: string
  email: string
  notification_enabled: boolean
}

export interface Subscription {
  id: number
  user_id: number
  bank_id: number | null
  currency_id: number | null
  bank?: Bank
  currency?: Currency
}

export interface RatesFilter {
  bank_id?: number | null
  currency_id?: number | null
}

export interface HistoryFilter {
  from?: string
  to?: string
  bank_id?: number | null
  currency_id?: number | null
  page?: number
  per_page?: number
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}
