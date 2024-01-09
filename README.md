
# ESP32 mini robot controlled by PHP ML image recognizer

(new project still in early stages)

 ![28352b44-4033-45c3-8af1-afe78a940846](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/6ddc7989-ed41-4f9f-a47c-6d01cde96f72) 

Robot is connected to public server and sending images (even some data for example collision detection sensor in future) to server (POST method) and recieving commands from server (GET + json).

Specifications:
- 2x 360 servo
- step up to 5V
- charging IC
- battery
- esp32 + cam module
- some 3d prints
- tracks

Arduino code in ARDUINO folder, 3D models and stls in 3DMODELS folder.

## Machine learning side
Using library https://rubixml.com/ and image recognition example.
In ML folder you can find some basic scripts. Move your train images to folder data_labels and you can train your model using - train.php. You can run image recognizer based on your trained model - run.php. Script will run image recognition and send to server command for robot.


## Server side
![image](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/925fef43-a3e5-4a88-a864-c307fff8182a)
- live feed from CAM (around 2-5fps),
- image which can analyze ML (resized, croped, grayscale)
- selected images for training model + label + delete
- robot controls
- bottom buttons sends current image to training dataset with choosen label


More photos:

![a38e2e97-7549-419b-9136-a2ad657da988](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/e5cfbee2-d0d1-4405-9382-f93972876720)
![c2dfffa4-7743-4493-9481-cb464dea8f27](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/9e789ad8-d90b-4694-a342-e6f61030ea30)
![57baebf9-2066-4482-b5ba-316a03a4698d](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/9ba80f51-c099-410b-a2de-7ec7c34f7663)
![16f54dc8-97f7-47a1-88f8-b8bb7482837d](https://github.com/bbtrash/arduino-robot-php-ml/assets/2698552/99094f86-e675-4e49-b400-717fdd04a24d)
