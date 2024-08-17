<template>
  <div class="search-container">
    <div class="category-input">
      <input
        v-model="localCategoryInput"
        type="text"
        :placeholder="`Search a venue in ${selectedCity} e.g. Accomodation > Hotel`"
        @click="onCategoryInput"
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
        {{ formatCategory(category) }}
        <button @click="removeCategory(category)">x</button>
      </span>
    </div>
    <ul v-if="filteredCategories.length" class="suggestions">
      <li v-for="category in filteredCategories" :key="category" @click="selectCategory(category)">
        {{ formatCategory(category) }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    selectedCity: {
      type: String,
      required: true
    },
    selectedCategories: {
      type: Array,
      default: () => []
    },
    categoryInput: {
      type: String,
      default: ''
    },
    filteredCategories: {
      type: Array,
      default: () => []
    },
    isLoading: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      localCategoryInput: this.categoryInput
    }
  },
  watch: {
    /**
     * Watches for changes to the `categoryInput` prop and updates the local data property.
     * @param {string} newVal - The new value of the `categoryInput` prop.
     */
    categoryInput (newVal) {
      this.localCategoryInput = newVal
    }
  },
  methods: {
    /**
     * Emits the `category-input` event with the current input value.
     * Triggered on input change and click events in the category input field.
     */
    onCategoryInput () {
      this.$emit('category-input', this.localCategoryInput)
    },

    /**
     * Emits the `select-category` event with the selected category.
     * @param {string} category - The category to select.
     */
    selectCategory (category) {
      this.$emit('select-category', category)
    },

    /**
     * Emits the `remove-category` event with the category to remove.
     * @param {string} category - The category to remove.
     */
    removeCategory (category) {
      this.$emit('remove-category', category)
    },

    /**
     * Emits the `search-places` event to trigger a search for places.
     * Triggered by clicking the search button or pressing Enter.
     */
    searchPlaces () {
      this.$emit('search-places')
    },

    /**
     * Formats the category string for display.
     * Replaces dots with ' > ', underscores with spaces, and capitalizes each word.
     * @param {string} category - The category string to format.
     * @returns {string} - The formatted category string.
     */
    formatCategory (category) {
      return category
        .replace(/\./g, ' > ')
        .replace(/_/g, ' ')
        .split(' ')
        .map(part => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ')
    }
  }
}
</script>

<style scoped>
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

.search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: none;
  cursor: pointer;
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
</style>
