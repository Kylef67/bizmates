<template>
  <div id="app">
    <div class="city-select">
      <select id="city-select" v-model="selectedCity" @change="onCityChange">
        <option v-for="(coords, city) in cities" :key="city" :value="city">
          {{ city }}
        </option>
      </select>
    </div>

    <SearchContainer
      :selected-city="selectedCity"
      :selected-categories="selectedCategories"
      :category-input="categoryInput"
      :filtered-categories="filteredCategories"
      :is-loading="isLoading"
      @category-input="onCategoryInput"
      @select-category="selectCategory"
      @remove-category="removeCategory"
      @search-places="searchPlaces"
    />

    <div id="map" style="height: 400px;" />

    <WeatherForecast v-if="selectedCityWeather" :city="selectedCity" :weather-data="selectedCityWeather" />
  </div>
</template>

<script>
import axios from 'axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import categories from '../geoapify/categories.json'
import cities from '../geoapify/cityLimits.json'
import SearchContainer from '../components/SearchContainer.vue'
import WeatherForecast from '../components/WeatherForecast.vue'

const searchResultIcon = L.icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34]
})

export default {
  name: 'WeatherComponent',
  components: {
    SearchContainer,
    WeatherForecast
  },
  data () {
    return {
      map: null,
      markers: {},
      cities,
      selectedCity: 'Tokyo',
      selectedCategories: ['accommodation.hotel'],
      categoryInput: '',
      availableCategories: categories,
      filteredCategories: [],
      weatherData: {},
      selectedCityWeather: null,
      isLoading: false,
      weatherApiKey: 'cacb4c0b9c48543cf6f78ba3be203b49',
      geoapifyApiKey: '44c4591e6901447fa142bd3a3a5ebdcf'
    }
  },
  mounted () {
    this.initMap()
  },
  methods: {
    /**
     * Initializes the Leaflet map and sets the default view.
     * Adds the Geoapify tile layer and performs initial actions like
     * setting the city and searching for places.
     */
    initMap () {
      this.map = L.map('map').setView([35.6895, 139.6917], 10) // Default to Tokyo

      L.tileLayer('/api/map-tile/{z}/{x}/{y}', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.geoapify.com/">Geoapify</a>'
      }).addTo(this.map)

      this.onCityChange()
      this.searchPlaces()
    },

    /**
     * Filters the available categories based on the input value.
     * Updates the `filteredCategories` data property.
     * @param {string} input - The input value to filter categories.
     */
    onCategoryInput (input) {
      this.filteredCategories = this.availableCategories.filter(category =>
        category.toLowerCase().includes(input)
      )
      if (!this.filteredCategories) {
        this.filteredCategories = this.availableCategories
      }
    },

    /**
     * Adds a category to the selected categories if not already present.
     * Clears the category input and filtered categories.
     * @param {string} category - The category to add.
     */
    selectCategory (category) {
      if (!this.selectedCategories.includes(category)) {
        this.selectedCategories.push(category)
      }
      this.categoryInput = ''
      this.filteredCategories = []
    },

    /**
     * Removes a category from the selected categories.
     * @param {string} category - The category to remove.
     */
    removeCategory (category) {
      this.selectedCategories = this.selectedCategories.filter(cat => cat !== category)
    },

    /**
     * Searches for places based on the selected categories and city.
     * Updates the markers on the map and handles search results.
     */
    async searchPlaces () {
      if (this.selectedCategories.length === 0 || !this.selectedCity) return

      this.isLoading = true

      const city = this.cities[this.selectedCity]

      try {
        const response = await axios.get('/api/search', {
          params: {
            bias: `proximity:${city.lon},${city.lat}`,
            categories: this.selectedCategories.join(',')
          }
        })
        this.handleSearchResults(response.data)
      } catch (error) {
        console.error('Error searching for places:', error)
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Handles the search results and updates the map with markers.
     * Clears existing markers and adds new ones based on the search results.
     * @param {Array} places - The search result places data.
     */
    handleSearchResults (places) {
      this.map.eachLayer(layer => {
        if (layer instanceof L.Marker) {
          this.map.removeLayer(layer)
        }
      })

      const markerCoordinates = []
      places.forEach(place => {
        const [lon, lat] = place.geometry.coordinates
        const marker = L.marker([lat, lon], { icon: searchResultIcon }).addTo(this.map)
        const popupContent = place.properties.formatted
          ? `${place.properties.name}<br>${place.properties.formatted}`
          : place.properties.name
        marker.bindPopup(popupContent)
        this.markers[place.properties.name] = marker
        markerCoordinates.push([lat, lon])
      })
      if (markerCoordinates.length > 0) {
        const bounds = L.latLngBounds(markerCoordinates)
        this.map.fitBounds(bounds)
      }
    },

    /**
     * Fetches the weather data for the selected city.
     * Updates the `selectedCityWeather` data property.
     * @param {string} city - The name of the city to fetch weather data for.
     */
    async fetchWeatherData (city) {
      try {
        const response = await axios.get('/api/forecast', {
          params: {
            city,
            units: 'metric'
          }
        })
        this.selectedCityWeather = response.data
      } catch (error) {
        console.error(`Error fetching weather data for ${city}:`, error)
      }
    },

    /**
     * Handles the city change event, updates the map view to the selected city,
     * and fetches the weather data for the new city.
     */
    onCityChange () {
      if (this.selectedCity) {
        const coords = this.cities[this.selectedCity]
        this.map.setView([coords.lat, coords.lon], 10)
        this.fetchWeatherData(this.selectedCity)
      }
    }
  }
}
</script>

<style scoped>
#app {
  font-family: Arial, sans-serif;
  text-align: center;
  margin: 0 auto;
  max-width: 1200px;
  padding: 20px;
}

h1 {
  margin-bottom: 20px;
}

.city-select {
  display: flex;
}

.city-select select {
  flex: 1;
  padding: 10px;
  font-size: 16px;
}

#map {
  height: 400px;
}
</style>
