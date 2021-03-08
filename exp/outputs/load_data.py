import pandas as pd


class LoadData:
    def __init__(self):
        self.idx = 0
        df = pd.read_csv('exp/outputs/mock_data.csv')
        self.data_y = df[[col for col in df.columns if col.startswith('data_y')]].T.values.tolist()
        self.data_x = df[[col for col in df.columns if col.startswith('data_x')]].T.values.tolist()
        self.integration_time = 1.
        self.max_intensity = 65535
        self.min_integration_time = 0.1 * 1e6 

    def get_spectrum(self):
        spectrum = ' '.join(str(e*self.integration_time) for e in self.data_y[self.idx])
        self.idx = 0 if len(self.data_y)-1 == self.idx else self.idx+1
        return spectrum

    def get_wavelengths(self):
        return ' '.join(str(e) for e in self.data_x[self.idx])

    def set_integration(self,integration_time):
        self.integration_time = integration_time

    def get_integration(self):
        return self.integration_time

    def get_max_intensity(self):
        return self.max_intensity

    def get_min_integration(self):
        return self.min_integration_time
