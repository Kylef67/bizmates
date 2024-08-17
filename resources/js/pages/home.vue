<template>
  <div id="app">
    <div class="city-select">
      <select id="city-select" v-model="selectedCity" placeholder="Select City" @change="onCityChange">
        <option v-for="(coords, city) in cities" :key="city" :value="city">
          {{ city }}
        </option>
      </select>
    </div>

    <div class="search-container">
      <div class="category-input">
        <input
          v-model="categoryInput"
          type="text"
          :placeholder="`Type in to search a venue in ${selectedCity}`"
          @input="onCategoryInput"
          @keydown.enter.prevent="searchPlaces"
        >
        <button class="search-icon" :disabled="isLoading" @click="searchPlaces">
          <i v-if="!isLoading" class="fas fa-2x fa-search" />
          <span v-if="isLoading" class="spinner" />
        </button>
      </div>
      <div class="chips">
        <span v-for="category in selectedCategories" :key="category" class="chip">
          {{ category }}
          <button @click="removeCategory(category)">x</button>
        </span>
      </div>
      <ul v-if="filteredCategories.length" class="suggestions">
        <li v-for="category in filteredCategories" :key="category" @click="selectCategory(category)">
          {{ category }}
        </li>
      </ul>
    </div>

    <div id="map" style="height: 400px;" />

    <div v-if="selectedCityWeather" class="weather">
      <h2>Weather Forecast for {{ selectedCity }}</h2>
      <div class="forecast-list">
        <div v-for="(dailyForecast, date) in groupedForecasts" :key="date" class="forecast-card">
          <h3>
            {{ new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
          </h3>
          <table>
            <thead>
              <tr>
                <th>Time</th>
                <th>Temperature</th>
                <th>Weather</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="forecast in dailyForecast" :key="forecast.dt">
                <td>{{ new Date(forecast.dt * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</td>
                <td>{{ forecast.main.temp }}°C</td>
                <td>
                  <i :class="getWeatherIcon(forecast.weather[0].icon)" />
                  {{ forecast.weather[0].description }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import categories from '../geoapify/categories.json'
import cities from '../geoapify/cityLimits.json'

const searchResultIcon = L.icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34]
})

export default {
  name: 'WeatherComponent',
  data () {
    return {
      map: null,
      markers: {},
      cities,
      selectedCity: 'Tokyo',
      selectedCategories: [],
      categoryInput: '',
      availableCategories: categories,
      filteredCategories: [],
      weatherData: {},
      selectedCityWeather: null,
      groupedForecasts: {},
      isLoading: false,
      weatherApiKey: 'cacb4c0b9c48543cf6f78ba3be203b49',
      geoapifyApiKey: '44c4591e6901447fa142bd3a3a5ebdcf'
    }
  },
  mounted () {
    this.initMap()
  },
  methods: {
    initMap () {
      this.map = L.map('map').setView([35.6895, 139.6917], 10) // Default to Tokyo

      L.tileLayer('/api/map-tile/{z}/{x}/{y}', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.geoapify.com/">Geoapify</a>'
      }).addTo(this.map)

      this.onCityChange()
    },
    onCategoryInput () {
      const input = this.categoryInput.toLowerCase()
      this.filteredCategories = this.availableCategories.filter(category =>
        category.toLowerCase().includes(input)
      )
      if (!this.filteredCategories) {
        this.filteredCategories = this.availableCategories
      }
    },
    selectCategory (category) {
      if (!this.selectedCategories.includes(category)) {
        this.selectedCategories.push(category)
      }
      this.categoryInput = ''
      this.filteredCategories = []
    },
    removeCategory (category) {
      this.selectedCategories = this.selectedCategories.filter(cat => cat !== category)
    },
    async searchPlaces () {
      if (this.selectedCategories.length === 0 || !this.selectedCity) return

      this.isLoading = true

      const city = this.cities[this.selectedCity]

      try {
        const response = await axios.get('https://api.geoapify.com/v2/places', {
          params: {
            bias: `proximity:${city.lon},${city.lat}`,
            categories: this.selectedCategories.join(','),
            apiKey: this.geoapifyApiKey
          }
        })
        this.handleSearchResults(response.data.features)
      } catch (error) {
        console.error('Error searching for places:', error)
      } finally {
        this.isLoading = false
      }
    },
    handleSearchResults (places) {
      this.map.eachLayer(layer => {
        if (layer instanceof L.Marker) {
          this.map.removeLayer(layer)
        }
      })

      places.forEach(place => {
        const [lon, lat] = place.geometry.coordinates
        const marker = L.marker([lat, lon], { icon: searchResultIcon }).addTo(this.map)
        marker.bindPopup(place.properties.name)
        this.markers[place.properties.name] = marker
      })
    },
    async fetchWeatherData (city) {
      try {
        const response = await axios.get('https://api.openweathermap.org/data/2.5/forecast', {
          params: {
            q: city,
            appid: this.weatherApiKey,
            units: 'metric'
          }
        })
        this.selectedCityWeather = response.data
        this.groupForecastsByDay()
      } catch (error) {
        console.error(`Error fetching weather data for ${city}:`, error)
      }
    },
    groupForecastsByDay () {
      this.groupedForecasts = this.selectedCityWeather.list.reduce((acc, forecast) => {
        const date = new Date(forecast.dt * 1000).toISOString().split('T')[0]
        if (!acc[date]) {
          acc[date] = []
        }
        acc[date].push(forecast)
        return acc
      }, {})
    },
    getWeatherIcon (iconCode) {
      const iconMapping = {
        '01d': 'fas fa-sun',
        '01n': 'fas fa-moon',
        '02d': 'fas fa-cloud-sun',
        '02n': 'fas fa-cloud-moon',
        '03d': 'fas fa-cloud',
        '03n': 'fas fa-cloud',
        '04d': 'fas fa-cloud-meatball',
        '04n': 'fas fa-cloud-meatball',
        '09d': 'fas fa-cloud-showers-heavy',
        '09n': 'fas fa-cloud-showers-heavy',
        '10d': 'fas fa-cloud-sun-rain',
        '10n': 'fas fa-cloud-moon-rain',
        '11d': 'fas fa-bolt',
        '11n': 'fas fa-bolt',
        '13d': 'fas fa-snowflake',
        '13n': 'fas fa-snowflake',
        '50d': 'fas fa-smog',
        '50n': 'fas fa-smog'
      }
      return iconMapping[iconCode] || 'fas fa-question'
    },
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

.search-container {
  margin-bottom: 20px;
}

.category-input {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.category-input input {
  flex: 1;
  padding: 10px;
  font-size: 16px;
}

.city-select {
  display: flex;
}

.city-select select {
  flex: 1;
  padding: 10px;
  font-size: 16px;
}

.search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: none;
  cursor: pointer;
}

.search-icon img {
  width: 24px;
  height: 24px;
}

.search-icon:disabled {
  cursor: not-allowed;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.chips {
  margin-top: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip {
  display: flex;
  align-items: center;
  padding: 5px 10px;
  background-color: #f1f1f1;
  border-radius: 20px;
  font-size: 14px;
}

.chip button {
  margin-left: 10px;
  background: none;
  border: none;
  font-size: 14px;
  cursor: pointer;
}

.suggestions {
  list-style: none;
  padding: 0;
  margin: 10%;
  border: 1px solid #ddd;
  border-radius: 4px;
  position: absolute;
  left: 0;
  right: 0;
  background-color: #fff;
  max-height: 150px;
  overflow-y: auto;
  z-index: 1000;
  margin-top: -10px;
}

.suggestions li {
  padding: 10px;
  cursor: pointer;
}

.suggestions li:hover {
  background-color: #f0f0f0;
}

.search-container button {
  font-size: 16px;
  cursor: pointer;
}

.weather {
  margin-top: 20px;
}

.forecast-list {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
}

.forecast-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  width: 300px;
  box-sizing: border-box;
}

.forecast-card h3 {
  margin-top: 0;
}

.forecast-card table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.forecast-card th, .forecast-card td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}

.forecast-card th {
  background-color: #f1f1f1;
}

@media (max-width: 768px) {
  .forecast-card {
    width: 100%;
  }
}
</style>
