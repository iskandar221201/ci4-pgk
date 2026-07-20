/**
 * components.js — Alpine component definitions for CI4 Kit Views Layer
 * Depends on: api.js, error.js (must be loaded first)
 * Required load order: auth.js → error.js → api.js → components.js
 *
 * All Alpine logic lives here — no inline Alpine logic in PHP views.
 */

/**
 * dataTable(endpoint)
 * Fetches list data from the API with pagination and search support.
 *
 * @param {string} endpoint - API endpoint, e.g. '/api/users'
 */
function dataTable(endpoint) {
  return {
    data:        [],
    meta:        {},
    loading:     true,
    currentPage: 1,
    totalPages:  1,
    search:      '',

    async init() {
      await this.fetch()
    },

    async fetch() {
      this.loading = true
      try {
        const params = new URLSearchParams({
          page:   this.currentPage,
          search: this.search,
        })
        const res      = await api.get(`${endpoint}?${params}`)
        this.data      = Array.isArray(res) ? res : (res.data ?? [])
        this.meta      = res.meta ?? {}
        this.totalPages = res.meta?.total_pages ?? 1
      } catch (err) {
        errorHandler.catch(err)
      } finally {
        this.loading = false
      }
    },

    async changePage(page) {
      if (page < 1 || page > this.totalPages) return
      this.currentPage = page
      await this.fetch()
    },
  }
}

/**
 * formHandler(endpoint, method, redirectUrl)
 * Submits a form to the API, handles loading state, maps 422 errors per-field,
 * and redirects on success.
 *
 * @param {string}      endpoint    - API endpoint
 * @param {string}      method      - HTTP method (default: 'POST')
 * @param {string|null} redirectUrl - URL to redirect to after success (default: null)
 */
function formHandler(endpoint, method = 'POST', redirectUrl = null) {
  return {
    errors:       {},
    isSubmitting: false,

    async submit(data) {
      this.isSubmitting = true
      this.errors       = {}
      try {
        await api.request(method, endpoint, data)
        if (redirectUrl) {
          window.location.href = redirectUrl
        }
      } catch (err) {
        if (err && err.errors) {
          this.errors = err.errors
        }
      } finally {
        this.isSubmitting = false
      }
    },
  }
}

/**
 * confirmDialog()
 * Reusable confirmation modal before destructive actions (delete, etc.).
 */
function confirmDialog() {
  return {
    visible:   false,
    message:   '',
    onConfirm: null,

    open(message, callback) {
      this.message   = message
      this.onConfirm = callback
      this.visible   = true
    },

    confirm() {
      if (typeof this.onConfirm === 'function') {
        this.onConfirm()
      }
      this.visible = false
    },

    cancel() {
      this.visible = false
    },
  }
}

/**
 * exportPdf(endpoint, params)
 * Hits the export endpoint, streams the response as a blob, and triggers a browser download.
 *
 * @param {string} endpoint - PDF export API endpoint
 * @param {object} params   - Additional query params (optional)
 */
async function exportPdf(endpoint, params = {}) {
  const query = new URLSearchParams(params).toString()
  const url   = query ? `${endpoint}?${query}` : endpoint
  try {
    const res  = await fetch(url, {
      headers: {
        'Authorization': 'Bearer ' + auth.getToken(),
        'Accept':        'application/pdf',
      },
    })
    if (!res.ok) {
      errorHandler.show('Failed to download PDF. Please try again.')
      return
    }
    const blob    = await res.blob()
    const blobUrl = URL.createObjectURL(blob)
    const a       = document.createElement('a')
    a.href        = blobUrl
    a.download    = 'export.pdf'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(blobUrl)
  } catch (err) {
    errorHandler.catch(err)
  }
}

/**
 * detailFetcher(endpoint)
 * Fetches specific data for detail page.
 */
function detailFetcher(endpoint) {
  return {
    data: {},
    loading: true,
    async init() {
      try {
        const res = await api.get(endpoint)
        this.data = res.data ?? res
      } catch (err) {
        errorHandler.catch(err)
      } finally {
        this.loading = false
      }
    }
  }
}
