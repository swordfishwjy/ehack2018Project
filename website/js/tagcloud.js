// Tag Cloud
      window.onload = function() {
        try {
          TagCanvas.Start('myCanvas','tags',{
            textColour: null,
            outlineColour: '#64f560',
            reverse: true,
			textFont: 'Impact,"Arial Black",sans-serif',
            textColour: '#00f',
            textHeight: 25,
            depth: 0.8,
            maxSpeed: 0.05
          });
        } catch(e) {
          // something went wrong, hide the canvas container
          document.getElementById('myCanvasContainer').style.display = 'none';
        }
      };
	  