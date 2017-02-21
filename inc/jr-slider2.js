window.addEventListener('load', (function (d) {
  "use strict";

  var galleries = d.querySelectorAll('[data-gallery-id]'),
    galleryCount = galleries.length,
    galleryObjs = [];

/* helpers ------------------------------------------------------------------ */
  //eventlisten minifier
  function listen(el, ev, fn) {
    return el.addEventListener(ev, fn);
  }
  //handles multiple events
  function multiEvents(el, listenArr) {
    listenArr.forEach(function (listener) {
      listen(el, listener[0], listener[1]);
    });
  }
  //replace an elements content. delayed replacments gives time for transitions
  function replaceChildren(parent, newChild, delay) {
    parent.appendChild(newChild);
    setTimeout(function () {
      while (parent.firstChild && parent.firstChild !== newChild) {
        parent.removeChild(parent.firstChild);
      }
    }, delay);
  }
  //polyfill for el.complete
  function whenLoaded(img, fn) {
    if (img.complete) {
      return fn();
    } else {
      listen(img, 'load', function () {

        return fn();
      });
      listen(img, 'error', function (e) {
        console.log('error', e);
      });
    }
  }
  //preloads images
  function preload(src) {
    if (src) {
      var img  = d.createElement('img');
      img.src = src;
    }
  }
  // Returns a function, that, as long as it continues to be invoked, will not be triggered.
  // https://davidwalsh.name/javascript-debounce-function
  function debounce(func, wait, immediate) {
    var timeout;
    return function () {
      var context = this,
        args = arguments,
        later = function () {
          timeout = null;
          if (!immediate) { func.apply(context, args); }
        },
        callNow = immediate && !timeout;

      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) { func.apply(context, args); }
    };
  }
  // adds 'active' class to index element, and removes it from the others.
  function makeActive(list, n, activeStr) {
    list.getElementsByClassName(activeStr)[0].classList.remove(activeStr);
    list.children[n].classList.add(activeStr);
  }
  // scrolls the child element horizontally into view - if needed.
  function scrollXToView(parent, child) {
    var parentLeft = parent.scrollLeft,
      parentRight = parent.offsetWidth + parentLeft,
      childLeft = child.offsetLeft,
      childRight = child.offsetWidth + childLeft;

    if (parentLeft > childLeft) {
      parent.scrollLeft = childLeft;
    } else if (parentRight < childRight) {
      parent.scrollLeft -= parentRight - childRight;
    }
  }
  //updates an element's height to that of its first child
  function setHeightByChild(el) {
    var height = el.firstElementChild.clientHeight - 4; //to hide white borders from rounding

    el.style.height = height + 'px';
  }
/* gallery class ------------------------------------------------------------ */

  function Gallery(el) {
    var $this = this;
    this.init(el);

    multiEvents(el, [
      ['mouseover', this.hoverEvent.bind(this)],
      ['mouseout', this.hoverEvent.bind(this)],
      ['click', this.clickEvent.bind(this)]
    ]);
    listen(window, 'resize', debounce(function () {
      return setHeightByChild(this.els.main);
    }, 500).bind(this));
    whenLoaded(this.els.main.firstElementChild, function () {
      return setTimeout(function () {
        setHeightByChild($this.els.main);
      }, 300);
    });

    this.replay();
  }
  //build up the class object
  Gallery.prototype.init = function init(el) {
    var $this = this;

    this.timer = el.getAttribute('data-gallery-autoplay');
    this.els = {
      thumbnails: el.getElementsByClassName('gallery-links')[0].children,
      lists: el.getElementsByClassName('gallery-list'),
      main: el.getElementsByClassName('gallery-focus')[0]
    };
    this.focus = 0;
    this.setup = {
      rwdLg : el.getAttribute('data-gallery-rwd-lg'),
      rwdMd : el.getAttribute('data-gallery-rwd-md'),
      loadWait: false,
      hoverLock: false,
      anim: el.getAttribute('data-gallery-anim')
    };

    if (this.els.thumbnails.length > 1) {
      $this.rwdLoadImg(this.els.thumbnails[1]);
    }
  };
  //handles the user clicking on the slider
  Gallery.prototype.clickEvent = function (e) {
    var fn = e.target.getAttribute('data-go'),
      nav = this.nav();

    if (!this.setup.loadWait && fn) {
      this.setup.loadWait = true; // button spam prevention
      e.preventDefault();

      return (isFinite(fn)) ? nav.img(fn) : nav[fn]();
    }
  };
  //handles the hover
  Gallery.prototype.hoverEvent = function (e) {
    this.setup.hoverLock = e.type === 'mouseover';
  };
  //navigation controllers
  Gallery.prototype.nav = function () {
    var $this = this,
      $imgCount = this.els.thumbnails.length;

    return {
      img: function (n) {
        $this.focus = parseInt(n, 10);
        this.go();
      },
      prev: function () {
        $this.focus = ($imgCount + $this.focus - 1) % $imgCount;
        this.go();
      },
      next: function () {
        $this.focus = ($this.focus + 1) % $imgCount;
        this.go();
      },
      go: function () {
        var lists = $this.els.lists,
          newImg = $this.els.thumbnails[$this.focus],
          i,
          listCount = lists.length;

        for (i = 0; i < listCount; i++) {
          makeActive(lists[i], $this.focus, 'active');
          if (lists[i].classList.contains('gallery-thumbs')) {
            scrollXToView(lists[i], lists[i].children[$this.focus]);
          }
        }

        $this.setImg(newImg.href, newImg.getAttribute('data-gallery-srcset'));

        if ($this.focus < listCount) {
          $this.rwdLoadImg($this.els.thumbnails[$this.focus + 1]);
        }
      }
    };
  };
  // replaces the main image with the new image
  Gallery.prototype.setImg = function (newSrc, srcset) {
    var $this = this,
      frame = this.els.main,
      img = d.createElement('img'),
      delay = $this.setup.anim;

    frame.classList.add('loading');
    img.src = newSrc;
    img.setAttribute('srcset', srcset);

    $this.zoomSet(newSrc);

    whenLoaded(img, function () {
      replaceChildren(frame, img, delay);
      frame.classList.remove('loading');
      setTimeout(function () {
        $this.setup.loadWait = false;
        setHeightByChild(frame);
      }, (delay * 2));
    });
  };
  //responsively preloads based on screen resolution
  Gallery.prototype.rwdLoadImg = function (img) {

    var width = window.innerWidth,
      src = img.href;

    switch (true) {
    case (width < this.setup.rwdMd):
      src = img.getAttribute('data-gallery-md');
      break;
    case (width < this.setup.rwdLg):
      src = img.getAttribute('data-gallery-lg');
      break;
    default:
      break;
    }

    preload(src);
  };
  //bonus SCP zoom function
  Gallery.prototype.zoomSet = function (url) {
    var zoomBtn = d.getElementsByClassName('btn-zoom');

    if (zoomBtn.length > 0) {
      zoomBtn[0].href = url;
    }
  };

  // activates the lister if it is active
  Gallery.prototype.replay = function () {
    var $this = this;

    if ($this.timer !== 'false') {
      setTimeout(function () {
        if (!$this.setup.loadWait && !$this.setup.hoverLock) {
          $this.nav().next();
        }
        $this.replay();
      }, $this.timer);
    }
  };

  for (var i = 0; i < galleryCount; i++) {
    galleryObjs.push(new Gallery(galleries[i]));
  }

}(document)));
