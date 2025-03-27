# IoT AI-driven Smart Grocery Cart w/ Edge Impulse
#
# OpenMV Cam H7
#
# Provide a checkout-free shopping experience letting customers create an online shopping list
# from products in their cart and pay via email.
#
# By Kutluhan Aktar

import sensor, image, math, lcd
from time import sleep
from pyb import RTC, Pin, ADC, LED


# Initialize the camera sensor.
sensor.reset()
sensor.set_pixformat(sensor.RGB565) # or sensor.GRAYSCALE
sensor.set_framesize(sensor.QQVGA2) # Special 128x160 framesize for LCD Shield.

# Initialize the ST7735 1.8" color TFT screen.
lcd.init()

# Set the built-in RTC (real-time clock).
rtc = RTC()
rtc.datetime((2022, 12, 6, 5, 15, 35, 52, 0))

# RGB:
red = LED(1)
green = LED(2)
blue = LED(3)

# Joystick:
J_VRX = ADC(Pin('P6'))
J_SW = Pin("P9", Pin.IN, Pin.PULL_UP)

# Define the data holders.
j_x = 0
sw = 0

# Capture an image with the OpenMV Cam H7 as a sample.
def save_sample(name, color, leds):
    # Get the current date and time.
    date = rtc.datetime()
    date = ".{}_{}_{}_{}-{}-{}".format(date[0], date[1], date[2], date[4], date[5], date[6])
    # Take a picture with the given frame settings (160X160).
    sensor.set_framesize(sensor.B160X160)
    sample = sensor.snapshot()
    sleep(1)
    # Save the captured image.
    file_name = "/samples/" + name + date + ".jpg"
    sample.save(file_name, quality=20)
    adjustColor(leds)
    print("\nSample Saved: " + file_name + "\n")
    # Show a glimpse of the captured image on the ST7735 1.8" color TFT screen.
    sensor.set_framesize(sensor.QQVGA2)
    lcd_img = sensor.snapshot()
    lcd_img.draw_rectangle(0, 0, 128, 30, fill=1, color =(0,0,0))
    lcd_img.draw_string(int((128-16*len(name))/2), 3, name, color=color, scale=2)
    lcd_img.draw_rectangle(0, 130, 128, 160, fill=1, color =(0,0,0))
    lcd_img.draw_string(int((128-16*len("Saved!"))/2), 132, "Saved!", color=color, scale=2)
    lcd_img.draw_cross(64, 80, color=color, size=8, thickness=2)
    lcd.display(lcd_img)
    sleep(5)
    adjustColor((0,0,0))

def adjustColor(leds):
    if leds[0]: red.on()
    else: red.off()
    if leds[1]: green.on()
    else: green.off()
    if leds[2]: blue.on()
    else: blue.off()

def readJoystick():
    j_x = ((J_VRX.read() * 3.3) + 2047.5) / 4095
    j_x = math.ceil(j_x)
    sw = J_SW.value()
    return (j_x, sw)


while(True):
    # Display a real-time video stream on the ST7735 1.8" color TFT screen.
    sensor.set_framesize(sensor.QQVGA2)
    lcd_img = sensor.snapshot()
    lcd.display(lcd_img)

    # Read controls from the joystick.
    j_x, sw = readJoystick()

    # Save samples (images) distinguished by 'food' and 'drink' labels.
    if(j_x > 3):
        save_sample("Food", (255,0,255), (1,0,1))
    if(j_x < 2):
        save_sample("Drink", (0,255,255), (0,1,1))
