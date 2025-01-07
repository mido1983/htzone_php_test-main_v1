# JSON Processor

A web-based tool for processing, comparing, and improving JSON data structures.

## Features

- Compare two JSON sources from different APIs
- Detect and remove duplicate records
- Format keys consistently (camelCase or snake_case)
- Remove empty values
- Suggest structural improvements

## Installation

1. Clone the repository
bash
git clone https://github.com/yourusername/json-processor.git
cd json-processor

2. Install dependencies
bash
composer install

3. Run the application
bash
php -S localhost:8000 -t public

4. Configure your web server to point to the `public` directory

5. Open in your browser
json-processor/README.md
http://localhost/json-processor/public/

## Usage

1. Enter two API URLs that return JSON data
2. Click "Get Data" to fetch and display the JSON
3. Review suggested improvements
4. Select desired improvements
5. Click "Apply Improvements" to process the JSON

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Composer
- Modern web browser

## License

MIT License