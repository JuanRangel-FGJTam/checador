const debounce = (func, delay) => {
  if (!debounce.timeout) {
    debounce.timeout = null
  }

  clearTimeout(debounce.timeout)
  debounce.timeout = setTimeout(func, delay)
}

export {
  debounce
}
