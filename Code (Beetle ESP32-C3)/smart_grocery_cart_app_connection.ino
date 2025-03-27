         /////////////////////////////////////////////  
        //     IoT AI-driven Smart Grocery         // 
       //         Cart w/ Edge Impulse            //
      //           -----------------             //
     //            (Beetle ESP32-C3)            //           
    //             by Kutluhan Aktar           // 
   //                                         //
  /////////////////////////////////////////////

// 
// Provide a checkout-free shopping experience letting customers create an online shopping list from products in their cart and pay via email.
//
// For more information:
// https://www.theamplituhedron.com/projects/IoT_AI_driven_Smart_Grocery_Cart_w_Edge_Impulse
//
//
// Connections
// Beetle ESP32-C3 : 
//                                MFRC522
// D7   --------------------------- RST
// D2   --------------------------- SDA
// D6   --------------------------- MOSI
// D5   --------------------------- MISO
// D4   --------------------------- SCK
//                                OpenMV Cam H7
// D0   --------------------------- P4
// D1   --------------------------- P5
//                                5mm Common Anode RGB LED
// D21  --------------------------- R
// D8   --------------------------- G
// D9   --------------------------- B
//                                Buzzer
// D20  --------------------------- +   


// Include the required libraries:
#include <WiFi.h>
#include <SPI.h>
#include <MFRC522.h>

char ssid[] = "<_SSID_>";        // your network SSID (name)
char pass[] = "<_PASSWORD_>";    // your network password (use for WPA, or use as key for WEP)
int keyIndex = 0;                // your network key Index number (needed only for WEP)

// Define the server on LattePanda 3 Delta 864.
char server[] = "192.168.1.22";
// Define the web application path.
String application = "/smart_grocery_cart/shopping.php";

// Initialize the WiFiClient object.
WiFiClient client; /* WiFiSSLClient client; */

// Create the MFRC522 instance.
#define RST_PIN   7
#define SS_PIN    2
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Define the MFRC522 module key input.
MFRC522::MIFARE_Key key;

// RGB:
#define red_p 21
#define green_p 8
#define blue_p 9
#define pwm_frequency 5000
#define resolution 8 /* 8-bit resolution */

// Buzzer:
#define buzzer_pin 20

// Define the data holders:
#define SERIAL_BAUDRATE  115200
String lastRead = "", OpenMV_data = "", response = "", table_name = ""; 

void setup() {
  Serial.begin(SERIAL_BAUDRATE);
  // Initialize the hardware serial port (Serial1) to communicate with the OpenMV Cam H7.
  Serial1.begin(SERIAL_BAUDRATE ,SERIAL_8N1,/*RX =*/0,/*TX =*/1);

  // Adjust the PWM pin settings for the RGB LED.
  ledcSetup(3, pwm_frequency, resolution); ledcSetup(4, pwm_frequency, resolution); ledcSetup(5, pwm_frequency, resolution);
  ledcAttachPin(red_p, 3); ledcAttachPin(green_p, 4); ledcAttachPin(blue_p, 5);

  // Initialize the MFRC522 hardware:
  SPI.begin();          
  mfrc522.PCD_Init();
  Serial.println("\n----------------------------------\nApproximate a New Card or Key Tag : \n----------------------------------\n");
  delay(3000);

  // Connect to WPA/WPA2 network. Change this line if using open or WEP network:
  WiFi.begin(ssid, pass);
  // Attempt to connect to the WiFi network:
  while(WiFi.status() != WL_CONNECTED){
    // Wait for the connection:
    delay(500);
    Serial.print(".");
  }
  // If connected to the network successfully:
  Serial.println("Connected to the WiFi network successfully!");
  adjustColor(0,0,255);
}

void loop(){
  // If the customer approaches the given RFID tag to the MFRC522 module:
  read_UID();
  delay(500);
  if(lastRead != ""){
    // Remove spaces from the detected UID.
    lastRead.replace(" ", "_");
    // Send an HTML email to the customer's registered email address via the web application, including the list of the products added to the cart and the link to the payment page. 
    make_a_get_request("?table="+table_name+"&send_email=OK&tag="+lastRead);
    // Clear the latest detected UID.
    lastRead = "";
    adjustColor(0,0,0);
  }

  // If the OpenMV Cam H7 transfers a command via serial communication:
  get_data_from_OpenMV();
  if(OpenMV_data != ""){
    // If the customer requested to change the table name to the latest registered table name in the database:
    if(OpenMV_data == "get_table"){
      // Make an HTTP Get request to obtain the latest registered table name:
      make_a_get_request("?deviceChangeTable");
      if(response != ""){
        // Elicit and format the received table name:
        int delimiter_1, delimiter_2;
        delimiter_1 = response.indexOf("%");
        delimiter_2 = response.indexOf("%", delimiter_1 + 1);
        // Glean information as substrings.
        table_name = response.substring(delimiter_1 + 1, delimiter_2);
        Serial.println("\nLatest Registered Table Name Obtained: " + table_name);
      }
    }else{
      // Make an HTTP Get request directly with the received command:
      make_a_get_request("?table="+table_name+OpenMV_data);
    }
    // Clear the latest received command:
    OpenMV_data = "";
    adjustColor(0,0,0);
  }
}

void make_a_get_request(String request){
  // Connect to the web application named smart_grocery_cart. Change '80' with '443' if you are using SSL connection.
  if (client.connect(server, 80)){
    // If successful:
    Serial.println("\nConnected to the web application successfully!\n");
    // Create the query string:
    String query = application + request;
    // Make an HTTP Get request:
    client.println("GET " + query + " HTTP/1.1");
    client.println("Host: 192.168.1.22");
    client.println("Connection: close");
    client.println();
  }else{
    Serial.println("\nConnection failed to the web application!\n");
    adjustColor(255,0,0);
    delay(2000);
  }
  adjustColor(255,255,0);
  delay(2000); // Wait 2 seconds after connecting...
  // If there are incoming bytes available, get the response from the web application.
  response = "";
  while(client.available()) { char c = client.read(); response += c; }
  if(response != ""){ Serial.println(response); adjustColor(0,255,0); }
}

void get_data_from_OpenMV(){
  // Obtain the transferred data packet from the OpenMV Cam H7 via serial communication.
  if(Serial1.available() > 0){
    Serial.println("\nTransferred query from the OpenMV Cam H7:");
    OpenMV_data = "";
    OpenMV_data = Serial1.readString();
    Serial.println(OpenMV_data);
    adjustColor(0,51,0);
    delay(2000);
    tone(buzzer_pin, 500);
    delay(1000);
    noTone(buzzer_pin);
    delay(1000);
  }
}

int read_UID(){
  // Detect the new card or tag UID. 
  if ( ! mfrc522.PICC_IsNewCardPresent()) { 
    return 0;
  }
  if ( ! mfrc522.PICC_ReadCardSerial()) {
    return 0;
  }

  // Display the detected UID on the serial monitor.
  Serial.print("\n----------------------------------\nNew Card or Key Tag UID : ");
  for (int i = 0; i < mfrc522.uid.size; i++) {
    lastRead += mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ";
    lastRead += String(mfrc522.uid.uidByte[i], HEX);
  }
  lastRead.trim();
  lastRead.toUpperCase();
  Serial.print(lastRead);
  Serial.print("\n----------------------------------\n\n");
  adjustColor(255,0,255);
  mfrc522.PICC_HaltA();
  delay(2000);
  return 1;
}

void adjustColor(int r, int g, int b){
  ledcWrite(3, 255-r);
  delay(100);
  ledcWrite(4, 255-g);
  delay(100);
  ledcWrite(5, 255-b);
  delay(100);
}
