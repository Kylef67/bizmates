<template>
  <div v-if="weatherData" class="weather">
    <h2>Weather Forecast for {{ city }}</h2>
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
</template>

<script>
export default {
  name: 'WeatherForecast',
  props: {
    city: {
      type: String,
      required: true
    },
    weatherData: {
      type: Object,
      default: () => ({})
    }
  },
  data () {
    return {
      groupedForecasts: {}
    }
  },
  watch: {
    /**
     * Watches for changes in the `weatherData` prop and updates the `groupedForecasts` data property.
     * Groups the forecasts by day.
     * @param {Object} newValue - The new value of the `weatherData` prop.
     */
    weatherData: {
      immediate: true,
      handler (newValue) {
        if (newValue) {
          this.groupForecastsByDay()
        }
      }
    }
  },
  methods: {
    /**
     * Groups the weather forecasts by day.
     * Converts the forecast data into a structure where each key is a date, and the value is an array of forecasts for that day.
     */
    groupForecastsByDay () {
      this.groupedForecasts = this.weatherData.list.reduce((acc, forecast) => {
        const date = new Date(forecast.dt * 1000).toISOString().split('T')[0]
        if (!acc[date]) {
          acc[date] = []
        }
        acc[date].push(forecast)
        return acc
      }, {})
    },

    /**
     * Maps weather icon codes to Font Awesome icon classes.
     * @param {string} iconCode - The weather icon code (e.g., '01d', '02n').
     * @returns {string} - The corresponding Font Awesome icon class.
     */
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
    }
  }
}
</script>

<style scoped>
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
