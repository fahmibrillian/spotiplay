from pyspark.sql import SparkSession
from pyspark.sql.functions import from_json, col
from pyspark.sql.types import StructType, StructField, StringType, DoubleType
from pyspark.sql.functions import current_timestamp

spark = SparkSession.builder \
    .appName("SpotifyStreaming") \
    .config("spark.kafka.consumer.properties", "partition.assignment.strategy=range") \
    .getOrCreate()

# Define the schema for the streaming DataFrame
schema = StructType([
    StructField("track_id", StringType()),
    StructField("track_name", StringType()),
    StructField("artist_name", StringType()),
    StructField("album_image", StringType()),
    StructField("danceability", DoubleType()),
    StructField("energy", DoubleType()),
    StructField("loudness", DoubleType()),
    StructField("mode", DoubleType()),
    StructField("speechiness", DoubleType()),
    StructField("acousticness", DoubleType()),
    StructField("instrumentalness", DoubleType()),
    StructField("liveness", DoubleType()),
    StructField("valence", DoubleType()),
    StructField("tempo", DoubleType())
])

# Create the streaming DataFrame
df = spark.readStream \
    .format("kafka") \
    .option("kafka.bootstrap.servers", "localhost:9092") \
    .option("subscribe", "latest_tracks") \
    .option("startingOffsets", "latest") \
    .load() \
    .select(from_json(col("value").cast("string"), schema).alias("data")) \
    .selectExpr("data.*")
    
#add timestamp
df = df.withColumn("timestamp", current_timestamp())
    

# Print the streaming DataFrame to the console
# query = df.writeStream \
#     .outputMode("append") \
#     .format("console") \
#     .start()
    
df.createOrReplaceTempView("songs")
songs = spark.sql("SELECT * FROM songs")
# query = songs.writeStream \
#     .format("memory") \
#     .queryName("latest_played") \
#     .outputMode("append") \
#     .start()
query = songs.writeStream \
    .format("parquet") \
    .option("path", "/home/bigdata/spotiplay/data") \
    .option("checkpointLocation", "/home/bigdata/spotiplay/checkpoint") \
    .queryName("latest_played") \
    .outputMode("append") \
    .start()

query.awaitTermination()