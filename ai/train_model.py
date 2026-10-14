"""
Train Crop Disease Model for Eswatini Crops
This script downloads/loads the PlantVillage dataset (which closely matches conventional Eswatini crops:
Maize, Tomato, Potato, Bell Pepper, Cabbage) and trains a Transfer Learning CNN model using MobileNetV2.
"""

import os
import tensorflow as tf
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D, Dropout
from tensorflow.keras.models import Model
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# 1. Configuration for Eswatini crops dataset
IMG_SIZE = (224, 224)
BATCH_SIZE = 32
EPOCHS = 5
MODEL_SAVE_PATH = os.path.join(os.path.dirname(__file__), 'model', 'eswatini_crop_disease_model.h5')

# Relevant classes matching Eswatini Ministry of Agriculture guidelines
ESWATINI_CROP_CLASSES = [
    'Tomato___Early_blight',
    'Tomato___Late_blight',
    'Tomato___Tomato_Yellow_Leaf_Curl_Virus',
    'Tomato___healthy',
    'Corn_(maize)___Common_rust_',
    'Corn_(maize)___Northern_Leaf_Blight',
    'Corn_(maize)___healthy',
    'Potato___Early_blight',
    'Potato___Late_blight',
    'Potato___healthy',
    'Pepper,_bell___Bacterial_spot',
    'Pepper,_bell___healthy'
]

def build_model(num_classes):
    print("Building MobileNetV2 Transfer Learning Model for Eswatini Crops...")
    base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(224, 224, 3))
    
    # Freeze base model layers
    base_model.trainable = False
    
    x = base_model.output
    x = GlobalAveragePooling2D()(x)
    x = Dropout(0.2)(x)
    predictions = Dense(num_classes, activation='softmax')(x)
    
    model = Model(inputs=base_model.input, outputs=predictions)
    model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])
    return model

def main():
    os.makedirs(os.path.dirname(MODEL_SAVE_PATH), exist_ok=True)
    
    print("Dataset setup guide:")
    print("1. Download the PlantVillage dataset from Kaggle or TensorFlow Datasets.")
    print("2. Place images under ai/dataset/ with folders matching the ESWATINI_CROP_CLASSES.")
    
    dataset_dir = os.path.join(os.path.dirname(__file__), 'dataset')
    
    if os.path.exists(dataset_dir):
        print(f"Loading images from {dataset_dir}...")
        train_datagen = ImageDataGenerator(
            rescale=1./255,
            rotation_range=20,
            width_shift_range=0.2,
            height_shift_range=0.2,
            horizontal_flip=True,
            validation_split=0.2
        )
        
        train_generator = train_datagen.flow_from_directory(
            dataset_dir,
            target_size=IMG_SIZE,
            batch_size=BATCH_SIZE,
            class_mode='categorical',
            subset='training'
        )
        
        val_generator = train_datagen.flow_from_directory(
            dataset_dir,
            target_size=IMG_SIZE,
            batch_size=BATCH_SIZE,
            class_mode='categorical',
            subset='validation'
        )
        
        model = build_model(num_classes=train_generator.num_classes)
        print("Training model...")
        model.fit(train_generator, validation_data=val_generator, epochs=EPOCHS)
        
        model.save(MODEL_SAVE_PATH)
        print(f"Model saved successfully to {MODEL_SAVE_PATH}")
    else:
        print(f"Dataset directory '{dataset_dir}' not found. Please populate it to run custom training.")

if __name__ == '__main__':
    main()
