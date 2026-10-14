import os
import numpy as np
import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout
from PIL import Image

MODEL_DIR = os.path.join(os.path.dirname(__file__), 'model')
MODEL_PATH = os.path.join(MODEL_DIR, 'eswatini_crop_disease_model.h5')

# Eswatini crop classes matching Ministry of Agriculture guidelines
CLASSES = [
    'Tomato___Early_blight',
    'Tomato___Late_blight',
    'Tomato___healthy',
    'Corn_(maize)___Common_rust',
    'Corn_(maize)___Northern_Leaf_Blight',
    'Corn_(maize)___healthy',
    'Potato___Early_blight',
    'Potato___Late_blight',
    'Potato___healthy',
    'Pepper___Bacterial_spot',
    'Pepper___healthy',
    'Cabbage___Black_rot'
]

def generate_synthetic_crop_data(num_samples_per_class=20):
    """Generate synthetic leaf image features representing different crops & diseases."""
    print("Generating training dataset for Eswatini crop disease classification...")
    X = []
    y = []
    
    for class_idx, class_name in enumerate(CLASSES):
        for _ in range(num_samples_per_class):
            # Base green color for leaves with variations for disease spots
            img_data = np.zeros((128, 128, 3), dtype=np.uint8)
            img_data[:, :, 1] = np.random.randint(100, 220, (128, 128)) # Green channel
            
            if 'blight' in class_name.lower() or 'spot' in class_name.lower() or 'rot' in class_name.lower():
                # Add brown/yellowish spots for diseases
                spots_x = np.random.randint(10, 118, 5)
                spots_y = np.random.randint(10, 118, 5)
                for sx, sy in zip(spots_x, spots_y):
                    img_data[sx-8:sx+8, sy-8:sy+8, 0] = np.random.randint(120, 200) # Red
                    img_data[sx-8:sx+8, sy-8:sy+8, 1] = np.random.randint(80, 140)  # Green
                    img_data[sx-8:sx+8, sy-8:sy+8, 2] = 20                         # Blue
            elif 'rust' in class_name.lower():
                # Add reddish/orange rust spots
                spots_x = np.random.randint(10, 118, 8)
                spots_y = np.random.randint(10, 118, 8)
                for sx, sy in zip(spots_x, spots_y):
                    img_data[sx-5:sx+5, sy-5:sy+5, 0] = np.random.randint(200, 255) # High red
                    img_data[sx-5:sx+5, sy-5:sy+5, 1] = np.random.randint(100, 160) # Medium green
                    img_data[sx-5:sx+5, sy-5:sy+5, 2] = 10                          # Low blue

            X.append(img_data)
            y.append(class_idx)

    X = np.array(X, dtype=np.float32) / 255.0
    y = tf.keras.utils.to_categorical(np.array(y), num_classes=len(CLASSES))
    return X, y

def train_and_save_model():
    X, y = generate_synthetic_crop_data()
    
    print("Building CNN model architecture...")
    model = Sequential([
        Conv2D(32, (3, 3), activation='relu', input_shape=(128, 128, 3)),
        MaxPooling2D((2, 2)),
        Conv2D(64, (3, 3), activation='relu'),
        MaxPooling2D((2, 2)),
        Conv2D(128, (3, 3), activation='relu'),
        MaxPooling2D((2, 2)),
        Flatten(),
        Dense(128, activation='relu'),
        Dropout(0.3),
        Dense(len(CLASSES), activation='softmax')
    ])
    
    model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])
    
    print("Training Eswatini Crop Disease Model...")
    model.fit(X, y, epochs=5, batch_size=16, verbose=1)
    
    os.makedirs(MODEL_DIR, exist_ok=True)
    model.save(MODEL_PATH)
    print(f"\nModel successfully trained and saved to: {MODEL_PATH}")

    # Also save class labels text file for app.py
    labels_path = os.path.join(MODEL_DIR, 'class_labels.txt')
    with open(labels_path, 'w') as f:
        f.write('\n'.join(CLASSES))
    print(f"Class labels saved to: {labels_path}")

if __name__ == '__main__':
    train_and_save_model()
