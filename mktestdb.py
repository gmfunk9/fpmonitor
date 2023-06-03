import sqlite3
import random
from datetime import datetime, timedelta
from decimal import Decimal, ROUND_HALF_UP

# Set up database connection
db_path = 'app/database/database.db'
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Create a sample table
table_name = 'website_stats'
cursor.execute(f"CREATE TABLE IF NOT EXISTS {table_name} (id INTEGER PRIMARY KEY, timestamp DATETIME, response_code INTEGER, ttfb TEXT, total TEXT)")

# List of response codes
response_codes = [200, 201, 301, 400, 403, 404, 500]

# Generate data and insert into the table
start_date = datetime(2010, 1, 1)
current_date = datetime.now()

delta = timedelta(minutes=120)
while start_date < current_date:
    timestamp = start_date.strftime('%Y-%m-%d %H:%M:%S')
    response_code = random.choice(response_codes)  # Select a random response code from the list
    ttfb = Decimal(random.uniform(1, 5)).quantize(Decimal('0.00'), rounding=ROUND_HALF_UP)  # Generate random TTFB with 2 decimal places
    total = (ttfb + Decimal(random.uniform(0.01, 0.1))).quantize(Decimal('0.00'), rounding=ROUND_HALF_UP)  # Generate total as ttfb + random value with 2 decimal places

    cursor.execute(f"INSERT INTO {table_name} (timestamp, response_code, ttfb, total) VALUES (?, ?, ?, ?)", (timestamp, response_code, str(ttfb), str(total)))
    print(f"Data entry created: timestamp={timestamp}, response_code={response_code}, ttfb={ttfb}, total={total}")
    start_date += delta

# Commit the changes and close the database connection
conn.commit()
conn.close()

print("Database created and populated with data every 30 minutes.")
