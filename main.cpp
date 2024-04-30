//Include libraries
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <WiFiClientSecure.h> 
#include <DHTesp.h>

//Add WIFI data
const char* ssid = "marisol";              //Add your WIFI network name 
const char* password =  "12345678";       //Add WIFI password
//const char* ssid = "vodafone4168";              //Add your WIFI network name 
//const char* password =  "E2NMJGM2QDJDMV";       //Add WIFI password

//Variables used in the code
String LED_id = "1";                  //Just in case you control more than 1 LED
String data_to_send = "";             //Text data to send to the server
unsigned int Actual_Millis, Previous_Millis;
int refresh_time = 2000;               //Refresh rate of connection to website (recommended more than 1s)
const int httpsPort = 443;            //HTTPS= 443
//SHA1 finger print of certificate use web browser to view and copy
const char fingerprint[] PROGMEM = "AE E2 33 AD AE 6E D9 BE 6A FA 5B 6F C9 62 35 BB A6 43 D4 31";

// NodeCMU Pinout https://www.luisllamas.es/esp8266-nodemcu/
int LED = 2;                      // GPIO2 - D4  . LED
int Rele_Pin = 5;                 // GPIO5 - D1  - Relay
int DHT11_PIN = 2;                // GPIO2 - D4  . DHT11
int DHT11b_PIN = 1;               // GPIO1 - D10 . DHT11b

DHTesp dht, dhtb;

void setup() {
  Serial.begin(9600);                   //Start monitor
  pinMode(LED, OUTPUT);                 //Set pin 2 as OUTPUT
  pinMode(Rele_Pin, OUTPUT);            // Set Rele_Pin as OUTPUT

  // Rele is off when output is HIGH
  digitalWrite(LED, HIGH);  
  digitalWrite(Rele_Pin, HIGH); 

  dht.setup(DHT11_PIN, DHTesp::DHT11);    // Connect DHT sensor
  dhtb.setup(DHT11b_PIN, DHTesp::DHT11);    // Connect DHT sensor  

  WiFi.begin(ssid, password);             //Start wifi connection
  Serial.print("Connecting...");
  while (WiFi.status() != WL_CONNECTED) { //Check for the connection
    delay(500);
    Serial.print(".");
  }

  Serial.print("Connected, my IP: ");
  Serial.println(WiFi.localIP());

  WiFi.setAutoReconnect(true);
  WiFi.persistent(true);

  Actual_Millis = millis();               //Save time for refresh loop
  Previous_Millis = Actual_Millis; 
}


void loop() {  
  //We make the refresh loop using millis() so we don't have to sue delay();
  Actual_Millis = millis();
  if(Actual_Millis - Previous_Millis > refresh_time){
    float humidity = dht.getHumidity();
    float temperature = dht.getTemperature();    
    //float humidityb = dhtb.getHumidity();
    //float temperatureb = dhtb.getTemperature();    
    
    Previous_Millis = Actual_Millis;
    Serial.print("Comprobando conexion ...");  
    
    if(WiFi.status()== WL_CONNECTED){                   //Check WiFi connection status  

      // Mostramos datos de temp y humedad en consola
      Serial.print("Humedad\t");
      Serial.print(humidity, 1);
      Serial.print("\t\t");
      Serial.print("Temperatura\t");  
      Serial.print(temperature, 1);
      Serial.print("Humedad-b\t");
      //Serial.print(humidityb, 1);
      Serial.print("\t\t");
      Serial.print("Temperatura-b\t");  
      //Serial.print(temperatureb, 1);      
      Serial.print("\n");

      Serial.print("--Connected");

      WiFiClientSecure client;    //Declare object of class 
      //WiFiClient client;
      HTTPClient http;

      // conexión segura =============================================
      client.setFingerprint(fingerprint);
      client.setTimeout(15000); // 15 Seconds
      delay(1000);
  
      Serial.print("HTTPS Connecting");
      int r=0; //retry counter
      while((!client.connect("ieslamarisma.net", 443)) && (r < 3)){
        delay(100);
        Serial.print(".");
        r++;
      }
      if(r==30) {
        Serial.println("Connection failed");
      }
      else {
        Serial.println("Connected to web");
      }
      //conexion segura =============================================

      // Datos a enviar
      data_to_send = "check_LED_status=" + LED_id;    //If button wasn't pressed we send text: "check_LED_status"
      
      // Agregamos temperatura
      data_to_send = data_to_send + "&temp=" + temperature;
      // Agregamos humedad
      data_to_send =  data_to_send + "&hum=" + humidity;
      // Agregamos temperatura-b
      //data_to_send = data_to_send + "&tempb=" + temperatureb;
      // Agregamos humedad-b
      //data_to_send =  data_to_send + "&humb=" + humidityb;            
      
      //Begin new connection to website   
      Serial.print("--Sending POST");    
      http.begin(client,"https://ieslamarisma.net/marisol/update.php");   //Indicate the destination webpage 
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");//Prepare the header    
      int response_code = http.POST(data_to_send);                        //Send the POST. This will giveg us a response code

      String response_body = http.getString();        
      Serial.print("Server reply: ");                                     //Print data to the monitor for debug
      Serial.println(response_body);      
      
      //If the code is higher than 0, it means we received a response
      if(response_code > 0){
        Serial.println("HTTP code " + String(response_code));             //Print return code

        if(response_code == 200){                                         //If code is 200, we received a good response and we can read the echo data
          //String response_body = http.getString();                        //Save the data comming from the website
          //Serial.print("Server reply: ");                                 //Print data to the monitor for debug
          //Serial.println(response_body);

          //If the received data is LED_is_on, we set LOW the LED & Relay pin
          if(response_body == "LED_is_on"){
            // LED & Rele are on when output is LOW
            digitalWrite(LED, LOW);  
            digitalWrite(Rele_Pin, LOW);             
          }
          //If the received data is LED_is_off, we set HIGH the LED & Relay pin
          else if(response_body == "LED_is_off"){
            // LED & Rele are off when output is HIGH
            digitalWrite(LED, HIGH);  
            digitalWrite(Rele_Pin, HIGH);            
          }  
        }//End of response_code = 200
      }//END of response_code > 0
      
      else{
       Serial.print("Error sending POST, code: ");
       Serial.println(response_code);
       Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(response_code).c_str());
      }
      http.end(); //End the connection
    }//END of WIFI connected
    else{
      Serial.println("WIFI connection error");
      Serial.println("Reconnecting to WiFi...");
      WiFi.disconnect();     
      WiFi.begin(ssid, password);
    }
  }
}