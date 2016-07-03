function binarySearch(arr, value) {
    var start = 0, end = arr.length - 1, mid;
    while (start <= end) {
        mid = 0 | (start + end) / 2;
        if (arr[mid] > value) end = mid - 1;
        if (arr[mid] === value) return mid;
        if (arr[mid] < value) start = mid + 1;
    }
    return -1;
}