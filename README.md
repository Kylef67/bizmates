# Weather and Places Search Application

## Overview

This project provides a dynamic web application for searching venues and viewing weather forecasts. The application leverages the Geoapify Maps API for venue search and the OpenWeatherMap API for weather data. The UI and UX implementation prioritize a clean and intuitive experience, ensuring users can easily search for places and view detailed weather forecasts for selected cities.

## UI and UX Implementation

### User Interface

- **City Selector**: Users can select cities from a dropdown, which dynamically updates the map view and weather forecast based on their selection.
- **Category Search**: The category input allows users to search for places based on predefined categories, with a user-friendly chip-style interface for selected categories.
- **Weather Forecast**: Displays a detailed 5-day weather forecast, grouped by day, including hourly temperature and weather conditions with corresponding icons.

### User Experience

- **Real-Time Updates**: As users type in the category search input or select a city, the application updates search results and weather forecasts in real time.
- **Responsive Design**: The layout adapts to various screen sizes, providing an optimal viewing experience on both desktop and mobile devices.
- **Visual Feedback**: The search input includes a loading spinner to indicate when data is being fetched, and error handling is in place for API failures.

### Code Implementation

- **Vue.js for Frontend**: The frontend is built using Vue.js, ensuring a reactive and component-based architecture for efficient UI updates and state management.
- **Leaflet for Maps**: Utilizes Leaflet for interactive maps, with custom markers and popups to enhance user experience.
- **Axios for API Calls**: Axios handles asynchronous requests to Geoapify and OpenWeatherMap, ensuring reliable data fetching and error handling.

## Live Demo

You can view a live demo of the application at [http://ec2-52-74-90-194.ap-southeast-1.compute.amazonaws.com/](http://ec2-52-74-90-194.ap-southeast-1.compute.amazonaws.com/).

## Setup

To set up the project locally, follow these steps:

1. **Clone the Repository**

    ```bash
    git clone <repository-url>
    cd <repository-directory>
    ```

2. **Set Up Environment Variables**
    ```bash
    GEOAPIFY_API_KEY=<your_geoapify_api_key>
    OPENWEATHERMAP_API_KEY=<your_openweathermap_api_key>
    ```

3. **Install Dependencies**
    ```bash
    composer install
    npm install
    npm run build or npm run dev

    ```


## Requirements

- **PHP: 8.0.3**
- **Node.js: 14.21.3**

## License

MIT

