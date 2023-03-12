from kafka import KafkaConsumer
from pymongo import MongoClient
import json

# set up MongoDB
client = MongoClient('mongodb://localhost:27017')
db = client['spotiplay']
collection = db['tracks']

# set up Kafka consumer
consumer = KafkaConsumer('recommendations', bootstrap_servers=['localhost:9092'])

# insert tracks into MongoDB
for message in consumer:
    track = json.loads(message.value.decode('utf-8'))
    print(track)