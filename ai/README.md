# Plant Doctor AI Microservice

This directory contains the Python microservice responsible for classifying crop diseases using a Convolutional Neural Network (CNN).

## Setup Instructions

1. Ensure Python 3.9+ is installed.
2. Open a terminal in this `ai/` directory.
3. Create a virtual environment:
   ```bash
   python -m venv venv
   ```
4. Activate the virtual environment:
   - Windows: `venv\Scripts\activate`
   - Linux/Mac: `source venv/bin/activate`
5. Install requirements:
   ```bash
   pip install -r requirements.txt
   ```
6. Run the server:
   ```bash
   python app.py
   ```
   
The Flask server will start on `http://localhost:5000`.

## Integration with PHP

The main PHP application at `farmer/plant_doctor.php` makes AJAX POST requests to `http://localhost:5000/predict` with the image payload.

## Training the Model

The CNN model should be trained using an Eswatini-specific dataset or a standard dataset like PlantVillage. 
You can add your `train_model.py` and Jupyter notebooks to the `model/` subdirectory here.
