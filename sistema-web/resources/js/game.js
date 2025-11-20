var width = 640;
var height = 480;

var main_stream = null;

var color_choices = [
  "#C7FC00",
  "#FF00FF",
  "#8622FF",
  "#FE0056",
  "#00FFCE",
  "#FF8000",
  "#00B7EB",
  "#FFFF00",
  "#0E7AFE",
  "#FFABAB",
  "#0000FF",
  "#CCCCCC",
];

// populate model select
var current_model_name = "food-ingredients-dataset";
var current_model_version = 3;
const API_KEY = "rf_tKs8TnnolIWeWcZWbBjG0mHskFl2";
const DETECT_API_KEY = "QcGpEdeu0qvl7Iykf60y";
const CAMERA_ACCESS_URL =
  "https://uploads-ssl.webflow.com/5f6bc60e665f54545a1e52a5/63d40cd1de273045d359cf9a_camera-access2.png";
const LOADING_URL =
  "https://uploads-ssl.webflow.com/5f6bc60e665f54545a1e52a5/63d40cd2210b56e0e33593c7_loading-camera2.gif";
var webcamLoop = false;
var no_detection_count = 0;
var canvas_painted = false;
var all_predictions = [];

var canvas = document.getElementById("video_canvas");
var ctx = canvas.getContext("2d");

const inferEngine = new inferencejs.InferenceEngine();
var modelWorkerId = null;

function detectFrame() {
  if (!modelWorkerId) return requestAnimationFrame(detectFrame);
  if (!canvas_painted) {
    var video_start = document.getElementById("video1");
    video_start.style.display = "block";
    canvas.style.display = "block";
    canvas.style.width = video_start.width + "px";
    canvas.style.height = video_start.height + "px";
    canvas.width = video_start.width;
    canvas.height = video_start.height;
    canvas.top = video_start.top;
    canvas.left = video_start.left;
    canvas.style.top = video_start.top + "px";
    canvas.style.left = video_start.left + "px";
    canvas.style.position = "absolute";
    canvas.style.display = "absolute";
    canvas_painted = true;
  }
  if (document.getElementById("loading_picture")) {
    document.getElementById("loading_picture").style.display = "none";
  }
  if (webcamLoop) {
    inferEngine
      .infer(modelWorkerId, new inferencejs.CVImage(video))
      .then(function (predictions) {
        requestAnimationFrame(detectFrame);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawBbox(ctx, video, predictions);
      });
  } else {
    main_stream.getTracks().forEach(function (track) {
      track.stop();
    });
    return true;
  }
}

async function getModel() {
  if (modelWorkerId != null) {
    await inferEngine.stopWorker(modelWorkerId);
  }
  modelWorkerId = null;
  return await inferEngine.startWorker(
    current_model_name,
    current_model_version,
    API_KEY
  );
}

document
  .getElementById("webcam-predict")
  .addEventListener("click", function () {
    // if video1, show it
    document.getElementById("example_demo").style.display = "none";
    if (document.getElementById("video1")) {
      document.getElementById("video1").style.display = "none";

    } else if (
      document.getElementById("video1") &&
      document.getElementById("video1").style.display == "block"


    ) {
      return;
    }
    // show picture canvas
    document.getElementById("picture_canvas").style.display = "block";
    webcamInference();
  });

var bounding_box_colors = {};

function setImageState(src, canvas = "picture_canvas") {
  var canvas = document.getElementById(canvas);
  var ctx = canvas.getContext("2d");
  var img = new Image();
  img.src = src;
  img.crossOrigin = "anonymous";
  img.style.width = width + "px";
  img.style.height = height + "px";
  img.height = height;
  img.width = width;
  img.onload = function () {
    ctx.drawImage(img, 0, 0, width, height, 0, 0, width, height);
  };
}

function drawBbox(ctx, video, predictions) {
  ctx.beginPath();
  var [sx, sy, sWidth, sHeight, dx, dy, dWidth, dHeight, scalingRatio] =
    getCoordinates(video);

  drawBoundingBoxes(predictions, video, ctx, scalingRatio, sx, sy);
}

function drawBoundingBoxes(
  predictions,
  canvas,
  ctx,
  scalingRatio,
  sx,
  sy,
  fromDetectAPI = false
) {
  if (predictions.length > 0) {
    all_predictions = predictions;
  }

  if (no_detection_count > 2) {
    all_predictions = predictions;
    no_detection_count = 0;
  }

  if (predictions.length == 0) {
    no_detection_count += 1;
  }

  for (var i = 0; i < predictions.length; i++) {
    var confidence = predictions[i].confidence;
    ctx.scale(1, 1);

    if (predictions[i].class in bounding_box_colors) {
      ctx.strokeStyle = bounding_box_colors[predictions[i].class];
    } else {
      // random color
      var color =
        color_choices[Math.floor(Math.random() * color_choices.length)];
      ctx.strokeStyle = color;
      // remove color from choices
      color_choices.splice(color_choices.indexOf(color), 1);

      bounding_box_colors[predictions[i].class] = color;
    }

    var prediction = predictions[i];
    var x = prediction.bbox.x - prediction.bbox.width / 2;
    var y = prediction.bbox.y - prediction.bbox.height / 2;
    var width = prediction.bbox.width;
    var height = prediction.bbox.height;

    if (!fromDetectAPI) {
      x -= sx;
      y -= sy;

      x *= scalingRatio;
      y *= scalingRatio;
      width *= scalingRatio;
      height *= scalingRatio;
    }

    // if box is partially outside 640x480, clip it
    if (x < 0) {
      width += x;
      x = 0;
    }

    if (y < 0) {
      height += y;
      y = 0;
    }

    // if first prediction, double label size

    ctx.rect(x, y, width, width);

    ctx.fillStyle = "rgba(0, 0, 0, 0)";
    ctx.fill();

    ctx.fillStyle = ctx.strokeStyle;
    ctx.lineWidth = "4";
    ctx.strokeRect(x, y, width, height);
    // put colored background on text
    var text = ctx.measureText(
      prediction.class + " " + Math.round(confidence * 100) + "%"
    );
    // if (i == 0) {
    //     text.width *= 2;
    // }

    // set x y fill text to be within canvas x y, even if x is outside
    // if (y < 0) {
    //     y = -40;
    // }
    if (y < 20) {
      y = 30;
    }

    // make sure label doesn't leave canvas

    ctx.fillStyle = ctx.strokeStyle;
    ctx.fillRect(x - 2, y - 30, text.width + 4, 30);
    // use monospace font
    ctx.font = "15px monospace";
    // use black text
    ctx.fillStyle = "black";

    ctx.fillText(
      prediction.class + " " + Math.round(confidence * 100) + "%",
      x,
      y - 10
    );
  }
}

function webcamInference() {
  // show loading_picture
  document.getElementById("loading_picture").style.display = "block";
  changeElementState([
    "picture_canvas",
    "prechosen_images_parent",
    "picture",
    "video1",
    "video",
    "video_canvas",
  ]);

  var constraints = {
    audio: false,
    video: {
      width: { ideal: 640 },
      height: { ideal: 480 },
      facingMode: "environment",
    },
  };

  navigator.mediaDevices
    // not user facing camera
    .getUserMedia(constraints)
    .then(function (stream) {
      main_stream = stream;

      var canvas = document.getElementById("video_canvas");
      var ctx = canvas.getContext("2d");
      // hide canvas
      // change to front-facing camera
      // delete video1 if exists
      if (document.getElementById("video1")) {
        video = document.getElementById("video1");
      } else {
        video = document.createElement("video");
        video.style.display = "none";
      }
      video.srcObject = stream;
      video.id = "video1";
      // hide video
      video.setAttribute("playsinline", "");
      video.setAttribute("muted", "");

      // add after canvas
      document.getElementById("video_canvas").after(video);

      video.onloadedmetadata = function () {
        video.play();
        // hide video
      };

      // on full load
      video.onplay = function () {
        const settings = stream.getVideoTracks()[0].getSettings();
        height = video.videoHeight;
        width = video.videoWidth;
        video.setAttribute("width", width);
        video.setAttribute("height", height);
        video.style.width = canvas.style.width + "px";
        video.style.height = canvas.style.height + "px";

        // canvas.style.width = width + "px";
        // canvas.style.height = height + "px";
        canvas.width = width;
        canvas.height = height;

        getModel()
          .then((workerId) => {
            modelWorkerId = workerId;
            video.style.display = "block";
            webcamLoop = true;
            var result = detectFrame();
            video_canvas.style.display = "block";

            if (result) {
              m.teardown();
              // disable webcam
              stream.getTracks().forEach(function (track) {
                track.stop();
              });
            }
          })
          .catch(function (err) {
            setImageState(CAMERA_ACCESS_URL, "video_canvas");
          });
      };

      ctx.scale(1, 1);
    })
    .catch(function (err) {
      setImageState(CAMERA_ACCESS_URL, "video_canvas");
    });
}

function getCoordinates(img) {
  var dx = 0;
  var dy = 0;
  var dWidth = 640;
  var dHeight = 480;

  var sy;
  var sx;
  var sWidth = 0;
  var sHeight = 0;

  var imageWidth = img.width;
  var imageHeight = img.height;

  const canvasRatio = dWidth / dHeight;
  const imageRatio = imageWidth / imageHeight;

  // scenario 1 - image is more vertical than canvas
  if (canvasRatio >= imageRatio) {
    var sx = 0;
    var sWidth = imageWidth;
    var sHeight = sWidth / canvasRatio;
    var sy = (imageHeight - sHeight) / 2;
  } else {
    // scenario 2 - image is more horizontal than canvas
    var sy = 0;
    var sHeight = imageHeight;
    var sWidth = sHeight * canvasRatio;
    var sx = (imageWidth - sWidth) / 2;
  }

  var scalingRatio = dWidth / sWidth;

  if (scalingRatio == Infinity) {
    scalingRatio = 1;
  }

  return [sx, sy, sWidth, sHeight, dx, dy, dWidth, dHeight, scalingRatio];
}

function changeElementState(elements, state = "none") {
  for (var i = 0; i < elements.length; i++) {
    if (document.getElementById(elements[i])) {
      document.getElementById(elements[i]).style.display = state;
    }
  }
}
