// user.js
const App = getApp()
Page({
  data: {
    loginStatus: {},
    userInfo: {},
    walletInfo: {},
    is_applied: false,
    DistributionInfo: {
      achievement: {
        "today": "请登录",
        "this_week": "请登录",
        "last_week": "请登录",
        "cumulative": "请登录"
      }
    },
    showCanvas: true,
    img: '',
    showcodMask: false,
    tgbg_url: 'https://huizhuandian.oss-cn-hangzhou.aliyuncs.com/uploads/20211111/4f2190eb8275c0ad73bd54a0de178c57.png',
    load_i: 0,
    load_i_len: 3,
    banner_img: {}
  },
  onShow() {
    App.isUserLogin((status) => {
      this.setData({
        loginStatus: status
      })
      this.setData({
        userInfo: wx.getStorageSync('userInfo')
      })
      if (status.status == 1) {
        this.getUserInfo()
        this.getMyDistribtionInfo()
        this.getMyDistributionInfo()
      }
    })
  },
  // 获取我的分销
  getMyDistribtionInfo() {
    App.getajax({
      url: "api/user/distribution/getDistributionInfo",
      success: res => {
        this.setData({
          distribtionInfo: res.data,
          img: res.data.invitation_qr_code
        })
      }
    })
  },
  // 获取我的业绩
  getMyDistributionInfo() {
    App.getajax({
      url: "api/user/distribution/getDistributionInfo",
      success: res => {
        this.setData({
          DistributionInfo: res.data
        })
      }
    })
  },
  lookMx() {
    this.isUserLogin(() => {
      wx.navigateTo({
        url: '/pages/extensionInfo/extensionInfo'
      })
    })
  },
  zhihuan() {
    wx.showToast({
      title: '敬请期待',
      icon: "none"
    })
  },
  // 展示推广码
  extension() {
    console.log(1)
    wx.showLoading({})
    this.setData({
      load_i: 0,
      showcodMask: true
    }, () => {
      this.init()
    })
    // wx.previewImage({
    //   url: this.data.distribtionInfo.invitation_qr_code,
    //   urls: [this.data.distribtionInfo.invitation_qr_code],
    // })
  },
  fenxiaoshuoming() {
    wx.navigateTo({
      url: '/pages/textDetail/textDetail?type=distribution_pictures',
    })
  },
  // 获取我的个人信息
  getUserInfo() {
    App.getajax({
      url: "api/user/center/getMyInfo",
      method: "GET",
      success: (res) => {
        if (res.code != 1) {
          wx.setStorage({
            key: "userInfo",
            data: ""
          })
        } else {
          this.setData({
            userInfo: res.data
          })
        }

      }
    })
  },
  // 判断用户是否登录
  isUserLogin(call) {
    App.isUserLogin((status) => {
      this.setData({
        loginStatus: status
      })
      if (status.status == 0) {
        wx.navigateTo({
          url: '/pages/login/login',
        })
      } else {
        call && call()
      }
    })
  },
  walletClick() {
    wx.navigateTo({
      url: '/pages/walletBalance/walletBalance'
    })
  },
  withdrawClick() {
    if (this.data.is_applied) {
      return
    }
    wx.navigateTo({
      url: '/pages/withdraw/withdraw'
    })
  },
  allOrderClick(e) {
    this.isUserLogin(() => {
      wx.navigateTo({
        url: '/pages/myOrder/myOrder?type=' + e.currentTarget.dataset.type + "&index=" + e.currentTarget.dataset.index
      })
    })
  },
  navCollect() {
    wx.navigateTo({
      url: '/pages/collect/collect',
    })
  },
  navFootprint() {
    wx.navigateTo({
      url: '/pages/footprint/footprint',
    })
  },
  addressClick() {
    this.isUserLogin(() => {
      wx.navigateTo({
        url: '/pages/shippingAddress/shippingAddress'
      })
    })

  },
  goExtensionList() {
    wx.navigateTo({
      url: '/pages/extensionList/extensionList'
    })
  },
  complaintsClick() {
    wx.navigateTo({
      url: '/pages/complaintsSuggestions/complaintsSuggestions'
    })
  },
  setClick() {
    wx.navigateTo({
      url: '/pages/set/set'
    })
  },
  evaluationClick() {
    wx.navigateTo({
      url: '/pages/evaluation/evaluation'
    })
  },
  onPullDownRefresh() {
    this.onShow()
  },
  // 保存图片
  create_canvas: function () {
    var self = this;
    wx.canvasToTempFilePath({
      canvasId: 'shareBox',
      success: function (res) {
        self.data.qrcode_img = res.tempFilePath;
        self.setData({
          showImg: res.tempFilePath,
          showCanvas: false
        })
      },
      fail: function (e) {
        wx.showToast({
          title: '生成海报失败',
          icon: 'none'
        });
      },
      complete: r => {
        wx.hideLoading();
      }
    });
  },
  // 保存图片
  save_canvas: function () {
    var self = this;
    wx.showLoading({
      title: '加载中...'
    });
    wx.saveImageToPhotosAlbum({
      filePath: self.data.qrcode_img,
      success: function () {
        wx.hideLoading();
        wx.showToast({
          title: '保存成功',
          icon: 'none'
        });
      }
    });
  }, // 防止点击穿透
  catchtouchmove() {},
  // 初始化开始
  init: function () {
    var self = this;
    console.log('init')
    self.get_screen_size();
    self.get_img_info(self.data.tgbg_url, 'banner_img');
    self.get_img_info(self.data.img, 'code_img');
  },
  // 初始化结束
  init_end: function () {
    var self = this;
    var obj = {};
    self.data.load_i++;
    if (self.data.load_i == self.data.load_i_len) {
      console.log(1)
      wx.hideLoading();
      self.drawImage();
    }
  },
  // 获取屏幕大小
  get_screen_size: function () {
    var self = this;
    var obj = {
      data: {
        width: 0,
        height: 0
      }
    };
    wx.getSystemInfo({
      success: function (res) {
        obj.data.width = res.windowWidth;
        obj.data.height = res.windowHeight;
        self.setData(obj);
        self.init_end();
      }
    });
  },
  // 获取图片信息
  get_img_info: function (url, str = '') {
    var self = this;
    var obj = {};
    wx.getImageInfo({
      src: url ? url : '1',
      success(res) {
        res.width = parseInt(res.width);
        res.height = parseInt(res.height);
        console.log(str)
        self.setData({
          [str]: res
        }, () => {
          self.init_end();
        });
      },
      fail(error) {},
      complete(ret) {}
    })
  },
  // 绘制画布
  drawImage: function () {
    var self = this;
    //创建一个canvas对象
    var ctx = wx.createCanvasContext('shareBox', this);

    // self.data.data.width / self.data.data.height;
    var canvasWidth = 230;
    var canvasHeight = 270;

    // 绘制图片【背景】
    ctx.beginPath();
    var banner_img = self.data.banner_img;
    // return
    ctx.drawImage(banner_img.path, 0, 0, canvasWidth, canvasHeight);
    ctx.closePath();

    // 绘制图片【二维码】
    ctx.beginPath();
    var code_img = self.data.code_img;
    var code_img_size = 98;
    ctx.drawImage(code_img.path, (canvasWidth / 2) - (code_img_size / 2), 85, code_img_size, code_img_size);
    // ctx.drawImage(code_img.path, 0, 0, code_img_size, code_img_size);
    ctx.closePath();

    ctx.draw(false, () => {
      self.create_canvas();
    });
  },
  // 关闭
  closeCodeMask() {
    this.setData({
      showcodMask: false
    })
  },
})