(function () {
  window.UEDITOR_HOME_URL = WebsitePath + "/static/editor/";
  var URL = window.UEDITOR_HOME_URL || getUEBasePath();
  window.UEDITOR_CONFIG = {
    UEDITOR_HOME_URL: URL,
    serverUrl: WebsitePath + "/upload_controller",
    toolbars: [
      [
        "fullscreen",
        "source",
        "|",
        "undo",
        "redo",
        "|",
        "bold",
        "italic",
        "underline",
        "fontborder",
        "strikethrough",
        "superscript",
        "subscript",
        "removeformat",
        "formatmatch",
        "autotypeset",
        "blockquote",
        "pasteplain",
        "|",
        "forecolor",
        "backcolor",
        "insertorderedlist",
        "insertunorderedlist",
        "selectall",
        "cleardoc",
        "|",
        "rowspacingtop",
        "rowspacingbottom",
        "lineheight",
        "|",
        "customstyle",
        "paragraph",
        "fontfamily",
        "fontsize",
        "|",
        "directionalityltr",
        "directionalityrtl",
        "indent",
        "|",
        "justifyleft",
        "justifycenter",
        "justifyright",
        "justifyjustify",
        "|",
        "touppercase",
        "tolowercase",
        "|",
        "link",
        "unlink",
        "anchor",
        "|",
        "imagenone",
        "imageleft",
        "imageright",
        "imagecenter",
        "|",
        "simpleupload",
        "insertimage",
        "emotion",
        "scrawl",
        "insertvideo",
        "music",
        "attachment",
        "map",
        "gmap",
        "insertframe",
        "insertcode",
        "pagebreak",
        "template",
        "background",
        "|",
        "horizontal",
        "date",
        "time",
        "spechars",
        "snapscreen",
        "wordimage",
        "|",
        "inserttable",
        "deletetable",
        "insertparagraphbeforetable",
        "insertrow",
        "deleterow",
        "insertcol",
        "deletecol",
        "mergecells",
        "mergeright",
        "mergedown",
        "splittocells",
        "splittorows",
        "splittocols",
        "charts",
        "|",
        "print",
        "preview",
        "searchreplace",
        "help",
        "drafts",
      ],
    ],

    lang: "zh-cn",
    langPath: URL + "../../language/",
    indentValue: "2em",
    enableAutoSave: false,
    saveInterval: 864000,
    emotionLocalization: true,
    listiconpath: URL + "themes/ueditor-list/",
    elementPathEnabled: false,
    wordCount: true,
    maximumWords: MaxPostChars,
    maxInputCount: 65535,
    topOffset: 56,
  };

  function getUEBasePath(docUrl, confUrl) {
    return getBasePath(
      docUrl || self.document.URL || self.location.href,
      confUrl || getConfigFilePath()
    );
  }

  function getConfigFilePath() {
    var configPath = document.getElementsByTagName("script");

    return configPath[configPath.length - 1].src;
  }

  function getBasePath(docUrl, confUrl) {
    var basePath = confUrl;

    if (/^(\/|\\\\)/.test(confUrl)) {
      basePath =
        /^.+?\w(\/|\\\\)/.exec(docUrl)[0] + confUrl.replace(/^(\/|\\\\)/, "");
    } else if (!/^[a-z]+:/i.test(confUrl)) {
      docUrl = docUrl
        .split("#")[0]
        .split("?")[0]
        .replace(/[^\\\/]+$/, "");

      basePath = docUrl + "" + confUrl;
    }

    return optimizationPath(basePath);
  }

  function optimizationPath(path) {
    var protocol = /^[a-z]+:\/\//.exec(path)[0],
      tmp = null,
      res = [];

    path = path.replace(protocol, "").split("?")[0].split("#")[0];

    path = path.replace(/\\/g, "/").split(/\//);

    path[path.length - 1] = "";

    while (path.length) {
      if ((tmp = path.shift()) === "..") {
        res.pop();
      } else if (tmp !== ".") {
        res.push(tmp);
      }
    }

    return protocol + res.join("/");
  }

  window.UE = {
    getUEBasePath: getUEBasePath,
  };
})();
