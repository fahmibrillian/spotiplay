from pyspark.sql import SparkSession
from pyspark.sql.types import StructType, StructField, StringType, DoubleType
from pyspark.sql.functions import current_timestamp
from pyspark.sql.functions import concat, col, lit
from pyspark.streaming import StreamingContext
from pyspark.streaming.kafka import KafkaUtils
from pyspark.ml import PipelineModel
from pyspark.mllib.linalg import Vectors
from pyspark.sql.functions import to_json, struct
import numpy as np
#pandas for time series
import pandas as pd
#time for counting time
import time
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

spark = SparkSession.builder \
    .appName("SpotifyStreaming") \
    .config("spark.jars.packages", "org.mongodb.spark:mongo-spark-connector_2.11:2.4.1,org.apache.spark:spark-sql-kafka-0-10_2.11:2.4.5") \
    .config("spark.kafka.consumer.properties", "partition.assignment.strategy=range") \
    .getOrCreate()
    
sc = spark.sparkContext

def euclidean_distance(x, y):
    """Calculate the Euclidean distance between two vectors."""
    distance = np.linalg.norm(np.array(x) - np.array(y))
    distance_in_int = int(distance * 100000)
    return distance_in_int


def get_recommendations(row, num_recommendations=5):
    spark2 = SparkSession.builder.appName("NewSpotifyStreaming2").getOrCreate()
    sc2= spark2.sparkContext
    ssc2 = StreamingContext(sc2, 10)
    ssc2.checkpoint("./checkpoint")
    
    output_df = spark2.read.parquet("/home/bigdata/spotiplay/tracks_clustered")
    music_database = output_df.rdd.map(lambda x: (x['name'], x['standardized'], x['prediction']))
    
    recommendations = []
    latest_song_name = row['track_name']
    latest_song_cluster = row['prediction']
    latest_song_features = row['standardized']

    # filter the music_database RDD to only include songs in the same cluster as the latest_song
    music_database_filtered = music_database.filter(lambda x: x[2] == latest_song_cluster)
    
    #filter the music not same as latest song
    music_database_filtered = music_database_filtered.filter(lambda x: x[0] != latest_song_name)

    # calculate the Euclidean distance between latest_song and each song in music_database_filtered
    music_database_distances = music_database_filtered.map(lambda x: (x[0], euclidean_distance(x[1], latest_song_features)))

    # sort music_database_distances by distance in ascending order
    music_database_sorted = music_database_distances.sortBy(lambda x: x[1], ascending=True)

    # remove duplicate songs from the list
    music_database_sorted = music_database_sorted.reduceByKey(lambda x, y: x)

    # get the top 5 songs
    recommendations_temp = music_database_sorted.take(num_recommendations)

    # add the recommendations to the recommendations list
    recommendations.extend([(recommendation[0], recommendation[1]) for recommendation in recommendations_temp])

    return recommendations

# create a Spark Streaming context with a batch interval of 10 seconds
ssc = StreamingContext(sc, 10)
ssc.checkpoint("./checkpoint")

# create a DStream that reads from Kafka
KAFKA_TOPIC = "latest_tracks"
BOOTSTRAP_SERVER = "localhost:9092"
songs = KafkaUtils.createDirectStream(ssc, [KAFKA_TOPIC],
                                      {"metadata.broker.list": BOOTSTRAP_SERVER})

#dataframe for counting time
df_wcluster = pd.DataFrame(columns=['index','time'])

def process(rdd):
    if rdd.isEmpty():
        print("RDD is empty")
    else:
        #time for counting time
        start_time = time.time()
        spark1 = SparkSession.builder.appName("NewSpotifyStreaming").getOrCreate()
        sc1= spark1.sparkContext
        ssc1 = StreamingContext(sc1, 10)
        ssc1.checkpoint("./checkpoint")
        print("========Streaming Input==========")

        json_df = spark1.read.json(rdd.map(lambda x: x[1]))
        cols = ['name', 'artists', 'danceability', 'energy', 'key', 'loudness', 'mode', 'speechiness', 'acousticness', 'instrumentalness', 'liveness', 'valence', 'tempo', 'duration_ms']
        cols_drop = ['track_id','album_image','timestamp','mode']
        latest = json_df.drop(*cols_drop)
        
        
        print(latest.show())
        
        # load the model from HDFS
        model = PipelineModel.load("model")
        transformed_df = model.transform(latest)
        transformed_df = transformed_df.toPandas()
        random_song = transformed_df.sample(n=1)
        random_song = random_song.to_dict('records')[0]
        recommendations = get_recommendations(random_song,5)
        recommendations_df = spark1.createDataFrame(recommendations, ['name', 'distance'])
        name = random_song['track_name']
        #add the random song name to the recommendations for key
        recommendations_df = recommendations_df.withColumn('key', lit(name))
        recommendations_df = recommendations_df \
            .withColumn("value", to_json(struct([col(x) for x in recommendations_df.columns]))) \
            .select("value")
    
        print(recommendations_df.show())
        
        #send to kafka again
        recommendations_df.write \
            .format("kafka") \
            .option("kafka.bootstrap.servers", "localhost:9092") \
            .option("topic", "recommendations") \
            .save()
            
        #time for counting time
        end_time = time.time()
        time_taken = end_time - start_time
        print("Time taken: ", time_taken)
        #append to dataframe
        df_wcluster.loc[len(df_wcluster)] = [len(df_wcluster), time_taken] 

result = songs.foreachRDD(process)

#count average of each feature
def average_feature(rdd):
    if rdd.isEmpty():
        print("RDD is empty")
    else:
        spark3 = SparkSession.builder.appName("NewSpotifyStreaming3").getOrCreate()
        sc3= spark3.sparkContext
        ssc3 = StreamingContext(sc3, 10)
        ssc3.checkpoint("./checkpoint")
        print("========Streaming Input==========")
        json_df = spark3.read.json(rdd.map(lambda x: x[1]))
        cols = ['name', 'artists', 'danceability', 'energy', 'key', 'loudness', 'mode', 'speechiness', 'acousticness', 'instrumentalness', 'liveness', 'valence', 'tempo', 'duration_ms']
        cols_drop = ['track_id','album_image','timestamp','mode']
        latest = json_df.drop(*cols_drop)
        latest = latest.toPandas()
        #average of each feature
        average_danceability = latest['danceability'].mean()
        average_energy = latest['energy'].mean()
        average_loudness = latest['loudness'].mean()
        average_speechiness = latest['speechiness'].mean()
        average_acousticness = latest['acousticness'].mean()
        average_instrumentalness = latest['instrumentalness'].mean()
        average_liveness = latest['liveness'].mean()
        average_valence = latest['valence'].mean()
        average_tempo = latest['tempo'].mean()
        #append to dataframe
        #create dataframe
        df_characteristic = pd.DataFrame(columns=['index','danceability','energy','loudness','speechiness','acousticness','instrumentalness','liveness','valence','tempo'])
        df_characteristic.loc[len(df_characteristic)] = [len(df_characteristic), average_danceability, average_energy, average_loudness, average_speechiness, average_acousticness, average_instrumentalness, average_liveness, average_valence, average_tempo]
        #send to kafka again
        df_characteristic = spark3.createDataFrame(df_characteristic)
        df_characteristic = df_characteristic \
            .withColumn("value", to_json(struct([col(x) for x in df_characteristic.columns]))) \
            .select("value")
        df_characteristic.write \
            .format("kafka") \
            .option("kafka.bootstrap.servers", "localhost:9092") \
            .option("topic", "characteristic") \
            .save()
        
character = songs.foreachRDD(average_feature)
ssc.start()
ssc.awaitTermination()