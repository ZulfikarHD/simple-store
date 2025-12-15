/**
 * usePhoneFormat Composable
 * Composable untuk format nomor telepon ke format internasional
 * dengan support multi-region berdasarkan konfigurasi toko
 *
 * Supported countries:
 * - ID (Indonesia): +62
 * - MY (Malaysia): +60
 * - SG (Singapore): +65
 * - PH (Philippines): +63
 * - TH (Thailand): +66
 * - VN (Vietnam): +84
 * - US (United States): +1
 * - AU (Australia): +61
 *
 * @author Zulfikar Hidayatullah
 */

import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Interface untuk konfigurasi country code
 */
interface CountryConfig {
    /** Kode negara ISO 3166-1 alpha-2 */
    code: string
    /** Nama negara dalam Bahasa Indonesia */
    name: string
    /** Dial code tanpa + (contoh: 62 untuk Indonesia) */
    dialCode: string
    /** Panjang digit lokal (tanpa dial code) */
    localDigits: number[]
    /** Prefix lokal yang perlu dihapus (contoh: 0 untuk Indonesia) */
    localPrefix: string
}

/**
 * Daftar konfigurasi negara yang didukung
 * dengan dial code dan format masing-masing
 */
export const COUNTRY_CONFIGS: Record<string, CountryConfig> = {
    ID: {
        code: 'ID',
        name: 'Indonesia',
        dialCode: '62',
        localDigits: [9, 10, 11, 12, 13],
        localPrefix: '0',
    },
    MY: {
        code: 'MY',
        name: 'Malaysia',
        dialCode: '60',
        localDigits: [9, 10, 11],
        localPrefix: '0',
    },
    SG: {
        code: 'SG',
        name: 'Singapore',
        dialCode: '65',
        localDigits: [8],
        localPrefix: '',
    },
    PH: {
        code: 'PH',
        name: 'Filipina',
        dialCode: '63',
        localDigits: [10, 11],
        localPrefix: '0',
    },
    TH: {
        code: 'TH',
        name: 'Thailand',
        dialCode: '66',
        localDigits: [9, 10],
        localPrefix: '0',
    },
    VN: {
        code: 'VN',
        name: 'Vietnam',
        dialCode: '84',
        localDigits: [9, 10],
        localPrefix: '0',
    },
    US: {
        code: 'US',
        name: 'Amerika Serikat',
        dialCode: '1',
        localDigits: [10],
        localPrefix: '',
    },
    AU: {
        code: 'AU',
        name: 'Australia',
        dialCode: '61',
        localDigits: [9],
        localPrefix: '0',
    },
}

/**
 * Default country code jika tidak dikonfigurasi
 */
const DEFAULT_COUNTRY = 'ID'

/**
 * usePhoneFormat composable
 * Menyediakan fungsi untuk format nomor telepon ke format internasional
 * berdasarkan konfigurasi negara dari store settings
 */
export function usePhoneFormat() {
    const page = usePage()

    /**
     * Computed untuk mendapatkan country code dari props atau default
     * Country code diambil dari Inertia shared props (store_settings.phone_country_code)
     */
    const countryCode = computed<string>(() => {
        const props = page.props as Record<string, unknown>

        // Cek dari store_settings shared props
        if (props.store_settings && typeof props.store_settings === 'object') {
            const settings = props.store_settings as Record<string, unknown>
            if (settings.phone_country_code && typeof settings.phone_country_code === 'string') {
                return settings.phone_country_code
            }
        }

        // Fallback ke default
        return DEFAULT_COUNTRY
    })

    /**
     * Computed untuk mendapatkan konfigurasi negara saat ini
     */
    const currentCountryConfig = computed<CountryConfig>(() => {
        return COUNTRY_CONFIGS[countryCode.value] || COUNTRY_CONFIGS[DEFAULT_COUNTRY]
    })

    /**
     * Format nomor telepon ke format internasional
     * untuk digunakan dengan WhatsApp API
     *
     * @param phone - Nomor telepon dalam format apapun
     * @param forceCountry - Optional: Override country code
     * @returns Nomor telepon dalam format internasional (tanpa +)
     *
     * @example
     * // Dengan country ID (Indonesia)
     * formatPhoneToInternational('08123456789') // '628123456789'
     * formatPhoneToInternational('628123456789') // '628123456789'
     * formatPhoneToInternational('+628123456789') // '628123456789'
     */
    function formatPhoneToInternational(phone: string, forceCountry?: string): string {
        // Hapus semua karakter non-digit
        let cleanPhone = phone.replace(/\D/g, '')

        // Ambil konfigurasi negara
        const config = forceCountry
            ? (COUNTRY_CONFIGS[forceCountry] || currentCountryConfig.value)
            : currentCountryConfig.value

        const { dialCode, localPrefix } = config

        // Jika sudah dimulai dengan dial code, return langsung
        if (cleanPhone.startsWith(dialCode)) {
            return cleanPhone
        }

        // Jika dimulai dengan local prefix (contoh: 0 untuk Indonesia)
        // ganti dengan dial code
        if (localPrefix && cleanPhone.startsWith(localPrefix)) {
            cleanPhone = dialCode + cleanPhone.substring(localPrefix.length)
        }
        // Jika tidak ada prefix, tambahkan dial code
        else if (!cleanPhone.startsWith(dialCode)) {
            cleanPhone = dialCode + cleanPhone
        }

        return cleanPhone
    }

    /**
     * Generate WhatsApp URL dengan nomor yang sudah diformat
     *
     * @param phone - Nomor telepon
     * @param message - Optional: Pesan yang akan dikirim
     * @returns URL WhatsApp yang siap dibuka
     */
    function getWhatsAppUrl(phone: string, message?: string): string {
        const formattedPhone = formatPhoneToInternational(phone)
        let url = `https://wa.me/${formattedPhone}`

        if (message) {
            url += `?text=${encodeURIComponent(message)}`
        }

        return url
    }

    /**
     * Buka WhatsApp dengan nomor yang sudah diformat
     *
     * @param phone - Nomor telepon
     * @param message - Optional: Pesan yang akan dikirim
     */
    function openWhatsApp(phone: string, message?: string): void {
        const url = getWhatsAppUrl(phone, message)
        window.open(url, '_blank')
    }

    /**
     * Format nomor telepon untuk display dengan format lokal
     * Contoh: 08123456789 -> 0812-3456-789
     *
     * @param phone - Nomor telepon
     * @returns Nomor telepon dengan format display
     */
    function formatPhoneForDisplay(phone: string): string {
        const cleanPhone = phone.replace(/\D/g, '')
        const config = currentCountryConfig.value

        // Untuk Indonesia, format: 0812-3456-7890
        if (config.code === 'ID') {
            if (cleanPhone.startsWith('62')) {
                const localNumber = '0' + cleanPhone.substring(2)
                return localNumber.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3')
            }
            if (cleanPhone.startsWith('0')) {
                return cleanPhone.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3')
            }
        }

        // Default: return as-is
        return phone
    }

    /**
     * Daftar semua negara yang didukung untuk dropdown selection
     */
    const supportedCountries = computed(() => {
        return Object.values(COUNTRY_CONFIGS).map(config => ({
            code: config.code,
            name: config.name,
            dialCode: `+${config.dialCode}`,
        }))
    })

    return {
        // State
        countryCode,
        currentCountryConfig,
        supportedCountries,

        // Methods
        formatPhoneToInternational,
        getWhatsAppUrl,
        openWhatsApp,
        formatPhoneForDisplay,

        // Constants
        COUNTRY_CONFIGS,
    }
}

/**
 * Standalone function untuk format nomor telepon
 * Berguna untuk digunakan di luar Vue component (misalnya di utils)
 *
 * @param phone - Nomor telepon
 * @param countryCode - Kode negara (default: ID)
 * @returns Nomor telepon dalam format internasional
 */
export function formatPhoneToInternational(phone: string, countryCode: string = DEFAULT_COUNTRY): string {
    let cleanPhone = phone.replace(/\D/g, '')

    const config = COUNTRY_CONFIGS[countryCode] || COUNTRY_CONFIGS[DEFAULT_COUNTRY]
    const { dialCode, localPrefix } = config

    if (cleanPhone.startsWith(dialCode)) {
        return cleanPhone
    }

    if (localPrefix && cleanPhone.startsWith(localPrefix)) {
        cleanPhone = dialCode + cleanPhone.substring(localPrefix.length)
    } else if (!cleanPhone.startsWith(dialCode)) {
        cleanPhone = dialCode + cleanPhone
    }

    return cleanPhone
}

