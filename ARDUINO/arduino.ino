/****************
Arduino code for robot based on https://RandomNerdTutorials.com/esp32-cam-projects-ebook/

Project original thread: https://www.reddit.com/r/ArduinoProjects/comments/18xfeij/mini_robot_esp32_cam_machine_learning_php/ for more info
*/

/*********
  Rui Santos
  Complete instructions at https://RandomNerdTutorials.com/esp32-cam-projects-ebook/
  
  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files.
  The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*********/

#include "esp_camera.h"
#include <WiFi.h>
#include "esp_timer.h"
#include "img_converters.h"
#include "Arduino.h"
#include "fb_gfx.h"
#include "soc/soc.h"             // disable brownout problems
#include "soc/rtc_cntl_reg.h"    // disable brownout problems
#include "esp_http_server.h"
#include <ServoEasing.hpp> // using servo easing for smooth moves :)
#include <Arduino_JSON.h> // json si fine


ServoEasing ServoRight;
ServoEasing ServoLeft;

JSONVar settingsData;

WiFiClient client;

// Replace with your network credentials
const char* ssid = "SSID";
const char* password = "PASSWORD";

#define PART_BOUNDARY "123456789000000000000987654321"
#define PWDN_GPIO_NUM    -1
#define RESET_GPIO_NUM   -1
#define XCLK_GPIO_NUM    21
#define SIOD_GPIO_NUM    26
#define SIOC_GPIO_NUM    27

#define Y9_GPIO_NUM      35
#define Y8_GPIO_NUM      34
#define Y7_GPIO_NUM      39
#define Y6_GPIO_NUM      36
#define Y5_GPIO_NUM      19
#define Y4_GPIO_NUM      18
#define Y3_GPIO_NUM       5
#define Y2_GPIO_NUM       4
#define VSYNC_GPIO_NUM   25
#define HREF_GPIO_NUM    23
#define PCLK_GPIO_NUM    22



String serverName = "SERVERIP";   // public server IP

String getPrevCommand = "";

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); //disable brownout detector

  // servo pins
  ServoRight.attach(32, 90);
  ServoLeft.attach(33, 90);

  Serial.begin(115200);
  Serial.setDebugOutput(false);
  
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 8000000;
  config.pixel_format = PIXFORMAT_JPEG; 
  
  if(psramFound()){
    config.frame_size = FRAMESIZE_VGA;
    config.jpeg_quality = 10;
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }


  // Camera init
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    return;
  }


  WiFi.begin(ssid, password);

  delay(500);


   
    int serverPort = 80;

    Serial.println("Connecting to server: " + serverName);

    // reconnection
    while (!client.connect(serverName.c_str(), serverPort)) {
        Serial.print(".");
        delay(10);
    }
    Serial.println("Connected");
      
  delay(500);
  
}

void loop() {

    int checkTimer = 0;

    String getAll;
    String getBody;

    String serverPath = "SERVER_IP_OR_NAME/img.php"; // php file for image upload
    String command = "stop"; // default command for robot

    camera_fb_t * fb = NULL;
    esp_camera_fb_return(fb); // dispose the buffered image
    fb = NULL; // reset to capture errors
    fb = esp_camera_fb_get(); // get fresh image
    if(!fb) {
        Serial.println("Camera capture failed");
        delay(1000);
        ESP.restart();
    }  
  

    String head = "--RandomNerdTutorials\r\nContent-Disposition: form-data; name=\"imageFile\"; filename=\"esp32-cam.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String tail = "\r\n--RandomNerdTutorials--\r\n";

    uint32_t imageLen = fb->len;
    uint32_t extraLen = head.length() + tail.length();
    uint32_t totalLen = imageLen + extraLen;

    
    // reconnection
    if (!client.available())
    {
      while (!client.connect(serverName.c_str(), 80)) {
        Serial.print(".");
        delay(10);
      }
    }

    // send image and data to server:
    client.println("POST " + serverPath + "?batt=3.2&charging=false HTTP/1.1"); //additional params placeholders
    client.println("Host: " + serverName);
    client.println("Content-Length: " + String(totalLen));
    client.println("Content-Type: multipart/form-data; boundary=RandomNerdTutorials");
    client.println();
    client.print(head);
  
    uint8_t *fbBuf = fb->buf;
    size_t fbLen = fb->len;
    for (size_t n=0; n<fbLen; n=n+1024) {
      if (n+1024 < fbLen) {
        client.write(fbBuf, 1024);
        fbBuf += 1024;
      }
      else if (fbLen%1024>0) {
        size_t remainder = fbLen%1024;
        client.write(fbBuf, remainder);
      }
    }   
    client.print(tail);
    
    esp_camera_fb_return(fb);
    
    int timoutTimer = 1000;
    long startTimer = millis();
    boolean state = false;

    // get data from server
    String getBodyJson;
    String getCommand;
    
    while ((startTimer + timoutTimer) > millis()) {
      Serial.print(".");
      delay(100);      
      while (client.available()) {
        char c = client.read();
      
        if (c == '|')
        {
          if (state == false)
          {
              state=true; 
          }
          else
          {
              getBodyJson += String(c);
              state=false;
          }
        }

        if (state==true) { getBodyJson += String(c); }
  
        startTimer = millis();
      }
      if (getBodyJson.length()>0) { break; }
    }

    getBodyJson.remove(getBodyJson.length()-1); // there is | on end
    getBodyJson.remove(0,1); // there is | on start
  
    JSONVar settingsData = JSON.parse(getBodyJson); // now just parse json
    if (JSON.typeof(settingsData) == "undefined")
    {
      Serial.println("eer json");
    }
    else
    {

        // get data from server, command (left,right,....), speed settings and accel
        getCommand = (const char*) settingsData["command"];

        Serial.println(getCommand);

        int speedRight = settingsData["speedRight"];
        int speedLeft = settingsData["speedLeft"];
        int delayTime = settingsData["delay"];
        int acceleration = settingsData["acceleration"];

        // command execution .. there will be more commands
        if (getCommand == "forward" && getPrevCommand != "forward")
        {
            // its hobby servos ... just trying to drive straight
            ServoRight.write(79); // -11
            delay(50);
            ServoLeft.write(110); // -15
            ServoRight.startEaseTo(speedRight, acceleration, START_UPDATE_BY_INTERRUPT);
            ServoLeft.startEaseTo(speedLeft, acceleration, START_UPDATE_BY_INTERRUPT);
        }

        if (getCommand == "left" && getPrevCommand != "left")
        {
          ServoRight.write(79);
          delay(50);
          ServoLeft.write(79);
          
          ServoRight.startEaseTo(speedRight, acceleration, START_UPDATE_BY_INTERRUPT);
          ServoLeft.startEaseTo(speedLeft, acceleration, START_UPDATE_BY_INTERRUPT);
          
        }

        if (getCommand == "right" && getPrevCommand != "right")
        {
          ServoRight.write(110);
          delay(50);
          ServoLeft.write(110);
          
          ServoRight.startEaseTo(speedRight, acceleration, START_UPDATE_BY_INTERRUPT);
          ServoLeft.startEaseTo(speedLeft, acceleration, START_UPDATE_BY_INTERRUPT);
          
        }

        if (getCommand == "backward" && getPrevCommand != "backward")
        {
          ServoRight.write(110);
          delay(50);
          ServoLeft.write(79);

          ServoLeft.startEaseTo(speedLeft, acceleration, START_UPDATE_BY_INTERRUPT);
          ServoRight.startEaseTo(speedRight, acceleration, START_UPDATE_BY_INTERRUPT);
        }

        if (getCommand == "stop" && getPrevCommand != "stop")
        {
          ServoRight.write(90);
          ServoLeft.write(90);
          ServoRight.startEaseTo(90, 50, START_UPDATE_BY_INTERRUPT);
          ServoLeft.startEaseTo(90, 50, START_UPDATE_BY_INTERRUPT);
        }

        getPrevCommand = getCommand;

    }


    
}
