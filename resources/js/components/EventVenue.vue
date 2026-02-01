<template>
  <div>
    <label class="block font-medium mb-1">会場名</label>
    <input
      id="autocompleteInput"
      ref="autocompleteInput"
      type="text"
      v-model="venueName"
      placeholder="場所を検索"
      class="border p-2 rounded w-full"
    />

    <div ref="map" style="width: 100%; height: 300px; margin-top: 16px;"></div>

    <!-- hidden （フォーム内にマウントされるのでPOSTされる） -->
    <input type="hidden" name="venue_name" :value="venueName" />
    <input type="hidden" name="latitude" :value="latitude" />
    <input type="hidden" name="longitude" :value="longitude" />
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  initialVenue: Object
})
const emit = defineEmits(['updateVenue'])

const map = ref(null)
const autocompleteInput = ref(null)
const venueName = ref(props.initialVenue?.name || '')
const latitude = ref(props.initialVenue?.lat || '')
const longitude = ref(props.initialVenue?.lng || '')

let mapInstance = null
let marker = null

/** Google Maps APIをロード */
function loadGoogleMapsScript(apiKey) {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`
    script.async = true
    script.defer = true
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

/** Google Maps + Autocomplete 初期化 */
async function initGoogleMaps() {
  // console.log('Google Maps 初期化開始')

  const res = await fetch('/api/google-api-key')
  const data = await res.json()
  const apiKey = data.key
  if (!apiKey) {
    console.error('APIキー取得失敗')
    return
  }

  await loadGoogleMapsScript(apiKey)
  await nextTick() // DOMが描画されるまで待つ

  const input = autocompleteInput.value
  // console.log('input:', input)
  // console.log('tagName:', input?.tagName)

  if (!input) {
    // console.warn('inputがまだnullです。0.3秒後に再試行。')
    setTimeout(initGoogleMaps, 300)
    return
  }

  const hasPosition =
    props.initialVenue?.lat &&
    props.initialVenue?.lng &&
    !isNaN(parseFloat(props.initialVenue.lat)) &&
    !isNaN(parseFloat(props.initialVenue.lng))

  const center = hasPosition
    ? { lat: parseFloat(props.initialVenue.lat), lng: parseFloat(props.initialVenue.lng) }
    : { lat: 35.6812, lng: 139.7671 } // 東京駅

  mapInstance = new google.maps.Map(map.value, {
    center,
    zoom: hasPosition ? 15 : 13,
  })

  if (hasPosition) {
    marker = new google.maps.Marker({
      map: mapInstance,
      position: center,
    })
  }

  const autocomplete = new google.maps.places.Autocomplete(input)
  autocomplete.bindTo('bounds', mapInstance)

  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace()
    if (!place.geometry || !place.geometry.location) return

    mapInstance.setCenter(place.geometry.location)
    mapInstance.setZoom(15)

    if (!marker) {
      marker = new google.maps.Marker({
        map: mapInstance,
        position: place.geometry.location,
      })
    } else {
      marker.setPosition(place.geometry.location)
    }

    venueName.value = place.name || ''
    latitude.value = place.geometry.location.lat()
    longitude.value = place.geometry.location.lng()

    emit('updateVenue', {
      name: venueName.value,
      lat: latitude.value,
      lng: longitude.value,
    })
  })
}

/** mount後に初期化 */
onMounted(async () => {
  await nextTick()
  initGoogleMaps()
})
</script>

<style scoped>
.h-64 {
  height: 16rem;
}
</style>
