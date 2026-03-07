import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'app.ministrify.church',
  appName: 'Ministrify',
  webDir: 'public',

  // Remote URL mode — the native shell loads the production site
  server: {
    url: 'https://ministrify.app',
    cleartext: false,
    allowNavigation: ['ministrify.app', '*.ministrify.app'],
  },

  android: {
    allowMixedContent: false,
    backgroundColor: '#ffffff',
    buildOptions: {
      keystorePath: 'ministrify-release.keystore',
      keystoreAlias: 'ministrify',
    },
  },

  plugins: {
    SplashScreen: {
      launchAutoHide: true,
      launchShowDuration: 2000,
      backgroundColor: '#3b82f6',
      showSpinner: false,
      splashImmersive: true,
      splashFullScreen: true,
    },
    StatusBar: {
      style: 'DEFAULT',
      backgroundColor: '#ffffff',
      overlaysWebView: false,
    },
    Keyboard: {
      resize: 'body',
      resizeOnFullScreen: true,
    },
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert'],
    },
  },
};

export default config;
