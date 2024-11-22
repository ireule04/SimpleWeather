const weather = {
    apiKey: "f179ee6faca65584816cd5fcf9bd2903",
    async fetchWeather(city) {
      try {
        const response = await fetch(`http://localhost/weather.php?q=${city}`);
        if (!response.ok) {
          throw new Error("No weather found.");
        }
        const data = await response.json();
        this.displayWeather(data);
        // Store data in local storage
        const weatherData = {
          temperature: Math.round(data.main.temp),
          cityName: data.name,
          date: new Date(),
          time: new Date().toLocaleTimeString(),
          humidity: data.main.humidity,
          windSpeed: data.wind.speed,
          description: data.weather[0].description
        };
  
        localStorage.setItem('weatherData', JSON.stringify(weatherData));
  
        // Show current data in local storage
        const storedData = JSON.parse(localStorage.getItem('weatherData'));
        console.log(storedData);
      } catch (error) {
        alert(error.message);
      }
    },
    displayWeather(data) {
      const { name, sys: { country } } = data;
      const { icon, description } = data.weather[0];
      const { temp, humidity } = data.main;
      const { speed } = data.wind;
      const temperature = Math.round(temp);
      const date = new Date().toDateString();
      
      document.querySelector(".city").innerText = name;
      document.querySelector(".description-text").innerText = description;
      document.querySelector(".temp").innerText = `${temperature}°C`;
      document.querySelector(".date").innerText = date;
      document.querySelector(".humidity").innerText = `${humidity}%`;
      document.querySelector(".wind").innerText = `${speed} km/h`;
      document.querySelector("#weather_icon").src = `https://openweathermap.org/img/wn/${icon}.png`;
      document.querySelector(".weather").classList.remove("loading");
      document.body.style.backgroundImage = `url('https://source.unsplash.com/1600x900/?${name}')`;
    },
    search() {
      const city = document.querySelector(".input").value;
      this.fetchWeather(city);
    },
  };
  
  const datetime = document.getElementById("datetime");
  
  function updateTime() {
    const now = new Date();
    const date = now.toDateString();
    const time = now.toLocaleTimeString();
    datetime.innerHTML = `${date} - ${time}`;
  }
  
  setInterval(updateTime, 1000);
  
  document.querySelector("#search").addEventListener("click", () => weather.search());
  document.querySelector(".input").addEventListener("keyup", (event) => {
    if (event.key === "Enter") {
      weather.search();
    }
  });
  
  weather.fetchWeather("Pokhara");
