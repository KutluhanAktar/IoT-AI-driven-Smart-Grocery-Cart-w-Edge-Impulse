# IoT AI-driven Smart Grocery Cart w/ Edge Impulse
#
# OpenMV Cam H7
#
# Provide a checkout-free shopping experience letting customers create an online shopping list
# from products in their cart and pay via email.
#
# By Kutluhan Aktar

import sensor, image, math, lcd, tf
from time import sleep
from pyb import Pin, ADC, LED, UART
from product_list import products

# Initialize the camera sensor.
sensor.reset()
sensor.set_pixformat(sensor.RGB565) # or sensor.GRAYSCALE
sensor.set_framesize(sensor.QQVGA2) # Special 128x160 framesize for LCD Shield.

# Define the required parameters to run an inference with the Edge Impulse FOMO model.
net = None
labels = None
min_confidence = 0.7

# Include the Edge Impulse FOMO model converted to an OpenMV firmware.
# Then, print errors, if any.
try:
    labels, net = tf.load_builtin_model('trained')
except Exception as e:
    raise Exception(e)

# Initiate the integrated serial port on the OpenMV Cam H7.
uart = UART(3, 115200, timeout_char=1000)

# Initialize the ST7735 1.8" color TFT screen.
lcd.init()

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
cart_choice = "EMPTY"
detected_product = 0

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
    menu_config = 0

    # Read controls from the joystick.
    j_x, sw = readJoystick()

    # Take a picture with the given frame settings.
    img = sensor.snapshot()
    lcd.display(img)

    # Change the table name on Beetle ESP32-C3 with the latest registered customer's table name in the database.
    if(sw == 0):
        query = "get_table"
        print("\nQuery: " + query + "\n")
        img.draw_rectangle(0, int(160/4), 128, int(160/2), fill=1, color =(0,0,255))
        img.draw_string(int((128-16*len("Query"))/2), int((160/2)-28), "Query", color=(255,255,255), scale=2)
        img.draw_string(int((128-16*len("Sent!"))/2), int((160/2)+8), "Sent!", color=(255,255,255), scale=2)
        lcd.display(img)
        uart.write(query)
        adjustColor((0,0,1))
        sleep(5)
        adjustColor((0,0,0))

    # Run an inference with the FOMO model to detect products.
    # Via the detect function, obtain all detected objects found in the recently captured image, split out per label (class).
    for i, detection_list in enumerate(net.detect(img, thresholds=[(math.ceil(min_confidence * 255), 255)])):
        # Exclude the class index 0 since it is the background class.
        if (i == 0): continue

        # If the Edge Impulse FOMO model predicted a label (class) successfully:
        if (len(detection_list) == 0): continue

        # Activate the selection (options) menu for adding or removing products to/from the database table.
        menu_config = 1

        # Obtain the detected product's label to retrieve its details saved in the given product list.
        detected_product = i

        # Get the prediction (detection) results and print them on the serial terminal.
        print("\n********** %s **********" % labels[i])
        for d in detection_list:
            # Draw a circle in the center of the detected objects (products).
            [x, y, w, h] = d.rect()
            center_x = math.floor(x + (w / 2))
            center_y = math.floor(y + (h / 2))
            img.draw_circle((center_x, center_y, 20), color=(255,0,255), thickness=3)
            print('\nDetected Label: %d | p: (%d, %d)' % (i, center_x, center_y))


    if(menu_config == 1):
        # Display the selection (options) menu.
        img.draw_rectangle(0, 0, 128, 30, fill=1, color =(0,0,0))
        img.draw_string(int((128-16*len("Add Cart"))/2), 3, "Add Cart", color=(0,255,0), scale=2)
        img.draw_rectangle(0, 130, 128, 160, fill=1, color =(0,0,0))
        img.draw_string(int((128-16*len("Remove"))/2), 135, "Remove", color=(255,0,0), scale=2)
        lcd.display(img)
        print("Selection Menu Activated!")
        while(menu_config == 1):
            j_x, sw = readJoystick()
            # Add the detected product to the database table.
            if(j_x > 3):
                cart_choice = "add"
                print("Selected Cart Option: " + cart_choice)
                img.draw_rectangle(0, 0, 128, 30, fill=1, color =(0,255,0))
                img.draw_string(int((128-16*len("Add Cart"))/2), 3, "Add Cart", color=(255,255,255), scale=2)
                img.draw_rectangle(0, 130, 128, 160, fill=1, color =(0,0,0))
                img.draw_string(int((128-16*len("Remove"))/2), 135, "Remove", color=(255,0,0), scale=2)
                lcd.display(img)
                adjustColor((0,1,0))
                sleep(1)
            # Remove the detected product from the database table.
            if(j_x < 2):
                cart_choice = "remove"
                print("Selected Cart Option: " + cart_choice)
                img.draw_rectangle(0, 0, 128, 30, fill=1, color =(0,0,0))
                img.draw_string(int((128-16*len("Add Cart"))/2), 3, "Add Cart", color=(0,255,0), scale=2)
                img.draw_rectangle(0, 130, 128, 160, fill=1, color =(255,0,0))
                img.draw_string(int((128-16*len("Remove"))/2), 135, "Remove", color=(255,255,255), scale=2)
                adjustColor((1,0,0))
                lcd.display(img)
                sleep(1)
            # Send the generated query (command), including the selected option (cart choice) and
            # the detected product's details, to the web application via Beetle ESP32-C3.
            if(sw == 0):
                query = "&"+cart_choice+"=OK&product_id="+products[detected_product]["id"]+"&product_name="+products[detected_product]["name"]+"&product_price="+products[detected_product]["price"]
                print("\nQuery: " + query + "\n")
                img.draw_rectangle(0, int(160/4), 128, int(160/2), fill=1, color =(0,0,255))
                img.draw_string(int((128-16*len("Query"))/2), int((160/2)-28), "Query", color=(255,255,255), scale=2)
                img.draw_string(int((128-16*len("Sent!"))/2), int((160/2)+8), "Sent!", color=(255,255,255), scale=2)
                lcd.display(img)
                uart.write(query)
                adjustColor((0,0,1))
                sleep(5)
                adjustColor((0,0,0))
                # Close the selection menu after transferring the product information and the selected option to the web application via Beetle ESP32-C3.
                menu_config = 0
                # Clear the detected label and the selected option (cart choice).
                detected_product = 0
                cart_choice = "EMPTY"
