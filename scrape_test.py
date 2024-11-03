import requests

url = "https://www.facebook.com/cointales/"

try:
    # Make a GET request to fetch the raw HTML content
    response = requests.get(url)
    
    # Check if the request was successful
    if response.status_code == 200:
        print(response.text)  # Print the HTML content of the page
    else:
        print(f"Failed to retrieve the page. Status code: {response.status_code}")

except requests.exceptions.RequestException as e:
    # Catch any requests-related exceptions
    print(f"An error occurred: {e}")
